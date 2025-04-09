<?php

namespace NeuronAI;

use NeuronAI\Chat\Messages\AssistantMessage;
use NeuronAI\Chat\Messages\Message;
use NeuronAI\Chat\Messages\ToolCallMessage;
use NeuronAI\Chat\Messages\Usage;
use NeuronAI\Observability\Events\MessageSaved;
use NeuronAI\Observability\Events\MessageSaving;

trait HandleStream
{
    public function stream(Message|array $messages): \Generator
    {
        $this->notify('stream-start');

        $messages = is_array($messages) ? $messages : [$messages];

        foreach ($messages as $message) {
            $this->notify('message-saving', new MessageSaving($message));
            $this->resolveChatHistory()->addMessage($message);
            $this->notify('message-saved', new MessageSaved($message));
        }

        $stream = $this->provider()
            ->systemPrompt($this->instructions())
            ->setTools($this->tools())
            ->stream(
                $this->resolveChatHistory()->getMessages(),
                function (ToolCallMessage $toolCallMessage) {
                    $toolCallResult = $this->executeTools($toolCallMessage);
                    yield from $this->stream([$toolCallMessage, $toolCallResult]);
                }
            );

        $content = '';
        $usage = new Usage(0, 0);
        foreach ($stream as $text) {
            // Catch usage when streaming
            $decoded = \json_decode($text, true);
            if (\is_array($decoded) && \array_key_exists('usage', $decoded)) {
                $usage->inputTokens += $decoded['usage']['input_tokens']??0;
                $usage->outputTokens += $decoded['usage']['output_tokens']??0;
                continue;
            }

            $content .= $text;
            yield $text;
        }

        $response = new AssistantMessage($content);
        $response->setUsage($usage);

        // Avoid double saving due to the recursive call.
        $history = $this->resolveChatHistory()->getMessages();
        $last = \end($history);
        if ($response->getRole() !== $last->getRole()) {
            $this->notify('message-saving', new MessageSaving($response));
            $this->resolveChatHistory()->addMessage($response);
            $this->notify('message-saved', new MessageSaved($response));
        }

        $this->notify('stream-stop');
    }
}
