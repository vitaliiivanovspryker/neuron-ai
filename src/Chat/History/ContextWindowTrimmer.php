<?php

declare(strict_types=1);

namespace NeuronAI\Chat\History;

use NeuronAI\Chat\Enums\MessageRole;
use NeuronAI\Chat\Messages\Message;
use NeuronAI\Chat\Messages\ToolCallMessage;
use NeuronAI\Chat\Messages\ToolCallResultMessage;
use NeuronAI\Chat\Messages\Usage;
use NeuronAI\Exceptions\ChatHistoryException;
use NeuronAI\Tools\ToolInterface;

class ContextWindowTrimmer implements TrimmerInterface
{
    public function trim(array $messages, int $contextWindow): array
    {
        // First validate the input
        if (!$this->validate($messages)) {
            throw new ChatHistoryException('Input messages array does not follow conversation integrity rules');
        }

        // If already within limits, return as-is
        if ($this->calculateTotalUsage($messages) <= $contextWindow) {
            return $messages;
        }

        // Create a working copy
        $trimmedMessages = $messages;

        // Trim messages while maintaining integrity
        while ($this->calculateTotalUsage($trimmedMessages) > $contextWindow && \count($trimmedMessages) > 2) {
            $removedAny = $this->removeOldestSafeMessage($trimmedMessages);

            if (!$removedAny) {
                throw new ChatHistoryException(
                    'Cannot trim messages to fit context window while maintaining conversation integrity. ' .
                    'Consider increasing context window size or reducing message complexity.'
                );
            }
        }

        // Final validation
        if (!$this->validate($trimmedMessages)) {
            throw new ChatHistoryException('Trimmed messages do not maintain conversation integrity');
        }

        if ($this->calculateTotalUsage($trimmedMessages) > $contextWindow) {
            throw new ChatHistoryException(
                'Unable to trim messages enough to fit within context window. ' .
                'Minimum conversation requires more tokens than available.'
            );
        }

        return $trimmedMessages;
    }

    public function validate(array $messages): bool
    {
        if ($messages === []) {
            return true; // Empty array is valid
        }

        // Rule 1: Must start with the user message
        $firstMessage = $messages[0];
        if ($firstMessage->getRole() !== MessageRole::USER->value) {
            return false;
        }

        // Rule 2: Must end with the assistant message (if more than one message)
        if (\count($messages) > 1) {
            $lastMessage = \end($messages);
            if ($lastMessage->getRole() !== MessageRole::ASSISTANT->value) {
                return false;
            }
        }

        // Rule 3: Validate tool call pairs
        return $this->validateToolCallPairs($messages);
    }

    public function calculateTotalUsage(array $messages): int
    {
        return \array_reduce($messages, function (int $carry, Message $message): int {
            if ($message->getUsage() instanceof Usage) {
                $carry += $message->getUsage()->getTotal();
            }
            return $carry;
        }, 0);
    }

    /**
     * Validate that all tool calls have corresponding results
     */
    protected function validateToolCallPairs(array $messages): bool
    {
        $pendingToolCalls = [];

        foreach ($messages as $index => $message) {
            if ($message instanceof ToolCallMessage) {
                // Track tool calls that need results
                $toolNames = \array_map(fn (ToolInterface $tool): string => $tool->getName(), $message->getTools());
                $pendingToolCalls[$index] = $toolNames;
            } elseif ($message instanceof ToolCallResultMessage) {
                // Find the matching tool call
                $resultToolNames = \array_map(fn (ToolInterface $tool): string => $tool->getName(), $message->getTools());

                $matchingCallIndex = null;
                foreach ($pendingToolCalls as $callIndex => $callToolNames) {
                    if ($callToolNames === $resultToolNames) {
                        $matchingCallIndex = $callIndex;
                        break;
                    }
                }

                if ($matchingCallIndex === null) {
                    return false; // Tool result without matching call
                }

                unset($pendingToolCalls[$matchingCallIndex]);
            }
        }

        // All tool calls must have results
        return $pendingToolCalls === [];
    }

    /**
     * Remove the oldest message that can be safely removed
     */
    protected function removeOldestSafeMessage(array &$messages): bool
    {
        // Need at least 2 messages to maintain conversation structure
        if (\count($messages) <= 2) {
            return false;
        }

        // Try to find removable messages starting from the oldest
        for ($i = 0; $i < \count($messages) - 2; $i++) {
            $message = $messages[$i];

            // Handle tool call pairs
            if ($message instanceof ToolCallMessage) {
                $toolResultIndex = $this->findCorrespondingToolResult($messages, $i);
                if ($toolResultIndex !== null && $toolResultIndex < \count($messages) - 1) {
                    // Remove both tool call and result (higher index first to avoid index shift)
                    \array_splice($messages, $toolResultIndex, 1);
                    \array_splice($messages, $i, 1);
                    return true;
                }
            } elseif ($this->canRemoveMessageSafely($messages, $i)) {
                // Check if we can remove this single message safely
                \array_splice($messages, $i, 1);
                return true;
            }
        }

        return false;
    }

    /**
     * Check if a message can be removed without breaking conversation flow
     */
    protected function canRemoveMessageSafely(array $messages, int $index): bool
    {
        // Can't remove the last two messages (need to end with assistant)
        if ($index >= \count($messages) - 2) {
            return false;
        }

        // Can't remove the first message if it would make conversation start with non-user
        if ($index === 0) {
            $nextMessage = $messages[1] ?? null;
            return $nextMessage && $nextMessage->getRole() === MessageRole::USER;
        }

        // Check if removing this message would break tool call pairs
        $message = $messages[$index];
        if ($message instanceof ToolCallMessage || $message instanceof ToolCallResultMessage) {
            return false; // Tool calls should only be removed in pairs
        }

        return true;
    }

    /**
     * Find the index of the tool result corresponding to a tool call
     */
    protected function findCorrespondingToolResult(array $messages, int $toolCallIndex): ?int
    {
        $toolCall = $messages[$toolCallIndex];

        if (!$toolCall instanceof ToolCallMessage) {
            return null;
        }

        $toolCallNames = \array_map(fn (ToolInterface $tool): string => $tool->getName(), $toolCall->getTools());
        // Look for tool results after the tool call
        $counter = \count($messages);

        // Look for tool results after the tool call
        for ($i = $toolCallIndex + 1; $i < $counter; $i++) {
            $message = $messages[$i];

            if ($message instanceof ToolCallResultMessage) {
                $toolCallResultNames = \array_map(fn (ToolInterface $tool): string => $tool->getName(), $message->getTools());
                if ($toolCallResultNames === $toolCallNames) {
                    return $i;
                }
            }
        }

        return null;
    }
}
