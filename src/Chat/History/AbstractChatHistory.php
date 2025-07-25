<?php

declare(strict_types=1);

namespace NeuronAI\Chat\History;

use NeuronAI\Chat\Attachments\Document;
use NeuronAI\Chat\Attachments\Image;
use NeuronAI\Chat\Enums\AttachmentContentType;
use NeuronAI\Chat\Enums\AttachmentType;
use NeuronAI\Chat\Enums\MessageRole;
use NeuronAI\Chat\Messages\AssistantMessage;
use NeuronAI\Chat\Messages\Message;
use NeuronAI\Chat\Messages\ToolCallMessage;
use NeuronAI\Chat\Messages\ToolCallResultMessage;
use NeuronAI\Chat\Messages\Usage;
use NeuronAI\Chat\Messages\UserMessage;
use NeuronAI\Tools\Tool;

abstract class AbstractChatHistory implements ChatHistoryInterface
{
    protected array $history = [];

    protected TokenCounterInterface $tokenCounter;

    public function __construct(
        protected int $contextWindow = 50000
    ) {
        $this->tokenCounter = new TokenCounter();
    }

    public function setTokenCounter(TokenCounterInterface $counter): ChatHistoryInterface
    {
        $this->tokenCounter = $counter;
        return $this;
    }

    public function addMessage(Message $message): ChatHistoryInterface
    {
        $this->history[] = $message;
        $this->storeMessage($message);

        $this->trimHistory();

        return $this;
    }

    abstract protected function storeMessage(Message $message): ChatHistoryInterface;

    public function getMessages(): array
    {
        return $this->history;
    }

    public function getLastMessage(): Message|false
    {
        return \end($this->history);
    }

    abstract public function removeOldMessage(int $index): ChatHistoryInterface;

    abstract protected function clear(): ChatHistoryInterface;

    public function flushAll(): ChatHistoryInterface
    {
        $this->clear();
        $this->history = [];
        return $this;
    }

    public function calculateTotalUsage(): int
    {
        return $this->tokenCounter->count($this->history);
    }

    protected function trimHistory(): void
    {
        if ($this->history === []) {
            return;
        }

        $tokenCount = $this->tokenCounter->count($this->history);

        // Early exit if all messages fit within the token limit
        if ($tokenCount <= $this->contextWindow) {
            $this->ensureValidMessageSequence();
            return;
        }

        // Binary search to find the maximum number of messages that fit
        $idx = $this->findMaxFittingMessages();

        $this->history = \array_slice($this->history, 0, $idx);

        // Ensure valid message sequence
        $this->ensureValidMessageSequence();
    }

    /**
     * Binary search to find the maximum number of messages that fit within token limit.
     */
    protected function findMaxFittingMessages(): int
    {
        $left = 0;
        $right = \count($this->history);
        $maxIterations = (int) \ceil(\log(\count($this->history), 2));

        for ($i = 0; $i < $maxIterations && $left < $right; $i++) {
            $mid = \intval(($left + $right + 1) / 2);
            $subset = \array_slice($this->history, 0, $mid);

            if ($this->tokenCounter->count($subset) <= $this->contextWindow) {
                $left = $mid;
            } else {
                $right = $mid - 1;
            }
        }

        return $left;
    }

    /**
     * Ensures the message list:
     * 1. Starts with a UserMessage
     * 2. Ends with an AssistantMessage
     * 3. Maintains tool call/result pairs
     */
    protected function ensureValidMessageSequence(): void
    {
        // First, ensure tool call/result pairs are complete
        $this->ensureCompleteToolPairs();

        // Then ensure it starts with a UserMessage
        $this->ensureStartsWithUser();

        // Finally, ensure it ends with an AssistantMessage
        $this->ensureEndsWithAssistant();
    }

    /**
     * Ensures tool call/result pairs are complete
     * If a ToolCallMessage is present, its corresponding ToolCallResultMessage must be included
     * If a ToolCallResultMessage is at the end without its ToolCallMessage, both are removed
     */
    protected function ensureCompleteToolPairs(): void
    {
        $result = [];
        $pendingToolCall = null;
        $pendingToolCallIndex = null;

        foreach ($this->history as $message) {
            if ($message instanceof ToolCallMessage) {
                // Store the tool call message temporarily
                $pendingToolCall = $message;
                $pendingToolCallIndex = \count($result);
                $result[] = $message;
            } elseif ($message instanceof ToolCallResultMessage) {
                if ($pendingToolCall instanceof ToolCallMessage) {
                    // We have a matching pair, add the result
                    $result[] = $message;
                    $pendingToolCall = null;
                    $pendingToolCallIndex = null;
                }
                // If no pending tool call, skip this orphaned result
            } else {
                // Regular message
                if ($pendingToolCall instanceof ToolCallMessage) {
                    // We have an incomplete tool call, remove it
                    \array_splice($result, $pendingToolCallIndex, 1);
                    $pendingToolCall = null;
                    $pendingToolCallIndex = null;
                }
                $result[] = $message;
            }
        }

        // Handle any remaining incomplete tool call at the end
        if ($pendingToolCall instanceof ToolCallMessage && $pendingToolCallIndex !== null) {
            \array_splice($result, $pendingToolCallIndex, 1);
        }

        $this->history = \array_values($result);
    }

    /**
     * Ensures the message list starts with a UserMessage.
     */
    protected function ensureStartsWithUser(): void
    {
        // Find the first UserMessage
        $firstUserIndex = null;
        foreach ($this->history as $index => $message) {
            if ($message instanceof UserMessage) {
                $firstUserIndex = $index;
                break;
            }
        }

        if ($firstUserIndex === null || $firstUserIndex === 0) {
            // No UserMessage found
            return;
        }

        if ($firstUserIndex > 0) {
            // Remove messages before the first UserMessage
            $this->history = \array_slice($this->history, $firstUserIndex);
        }
    }

    /**
     * Ensures the message list ends with an AssistantMessage.
     */
    protected function ensureEndsWithAssistant(): void
    {
        // Work backwards until we find an AssistantMessage (including ToolCallMessage)
        $count = \count($this->history);
        for ($i = $count - 1; $i >= 0; $i--) {
            if ($this->history[$i] instanceof AssistantMessage) {
                // Check if this is part of an incomplete tool pair
                if ($this->history[$i] instanceof ToolCallMessage) {
                    // Check if there's a result message after it
                    $hasResult = false;
                    for ($j = $i + 1; $j < $count; $j++) {
                        if ($this->history[$j] instanceof ToolCallResultMessage) {
                            $hasResult = true;
                            break;
                        }
                    }
                    // If no result, skip this ToolCallMessage
                    if (!$hasResult) {
                        continue;
                    }
                }
                $this->history = \array_slice($this->history, 0, $i + 1);
                return;
            }
        }
    }

    public function jsonSerialize(): array
    {
        return $this->getMessages();
    }

    protected function deserializeMessages(array $messages): array
    {
        return \array_map(fn (array $message): Message => match ($message['type'] ?? null) {
            'tool_call' => $this->deserializeToolCall($message),
            'tool_call_result' => $this->deserializeToolCallResult($message),
            default => $this->deserializeMessage($message),
        }, $messages);
    }

    protected function deserializeMessage(array $message): Message
    {
        $messageRole = MessageRole::from($message['role']);
        $messageContent = $message['content'] ?? null;

        $item = match ($messageRole) {
            MessageRole::ASSISTANT => new AssistantMessage($messageContent),
            MessageRole::USER => new UserMessage($messageContent),
            default => new Message($messageRole, $messageContent)
        };

        $this->deserializeMeta($message, $item);

        return $item;
    }

    protected function deserializeToolCall(array $message): ToolCallMessage
    {
        $tools = \array_map(fn (array $tool) => Tool::make($tool['name'], $tool['description'])
            ->setInputs($tool['inputs'])
            ->setCallId($tool['callId'] ?? null), $message['tools']);

        $item = new ToolCallMessage($message['content'], $tools);

        $this->deserializeMeta($message, $item);

        return $item;
    }

    protected function deserializeToolCallResult(array $message): ToolCallResultMessage
    {
        $tools = \array_map(fn (array $tool) => Tool::make($tool['name'], $tool['description'])
            ->setInputs($tool['inputs'])
            ->setCallId($tool['callId'])
            ->setResult($tool['result']), $message['tools']);

        return new ToolCallResultMessage($tools);
    }

    protected function deserializeMeta(array $message, Message $item): void
    {
        foreach ($message as $key => $value) {
            if ($key === 'role') {
                continue;
            }
            if ($key === 'content') {
                continue;
            }
            if ($key === 'usage') {
                $item->setUsage(
                    new Usage($message['usage']['input_tokens'], $message['usage']['output_tokens'])
                );
                continue;
            }
            if ($key === 'attachments') {
                foreach ($message['attachments'] as $attachment) {
                    switch (AttachmentType::from($attachment['type'])) {
                        case AttachmentType::IMAGE:
                            $item->addAttachment(
                                new Image(
                                    $attachment['content'],
                                    AttachmentContentType::from($attachment['content_type']),
                                    $attachment['media_type'] ?? null
                                )
                            );
                            break;
                        case AttachmentType::DOCUMENT:
                            $item->addAttachment(
                                new Document(
                                    $attachment['content'],
                                    AttachmentContentType::from($attachment['content_type']),
                                    $attachment['media_type'] ?? null
                                )
                            );
                            break;
                    }

                }
                continue;
            }
            $item->addMetadata($key, $value);
        }
    }
}
