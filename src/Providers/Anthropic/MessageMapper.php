<?php

namespace NeuronAI\Providers\Anthropic;

use NeuronAI\Chat\Messages\AssistantMessage;
use NeuronAI\Chat\Messages\Message;
use NeuronAI\Chat\Messages\ToolCallMessage;
use NeuronAI\Chat\Messages\ToolCallResultMessage;
use NeuronAI\Chat\Messages\UserMessage;
use NeuronAI\Exceptions\AgentException;
use NeuronAI\Providers\MessageMapperInterface;
use NeuronAI\Tools\ToolInterface;

class MessageMapper implements MessageMapperInterface
{
    public function map(Message $message): array
    {
        return match ($message::class) {
            UserMessage::class,
            AssistantMessage::class,
            ToolCallMessage::class => $this->mapMessage($message),
            ToolCallResultMessage::class => $this->mapToolsResult($message),
            default => throw new AgentException('Could not map message type '.$message::class),
        };
    }

    public function mapMessage(Message $message): array
    {
        $message = $message->jsonSerialize();

        if (\array_key_exists('usage', $message)) {
            unset($message['usage']);
        }

        if (\array_key_exists('type', $message)) {
            unset($message['type']);
        }

        return $message;
    }

    public function mapToolsResult(ToolCallResultMessage $message): array
    {
        return [
            'role' => Message::ROLE_USER,
            'content' => \array_map(function (ToolInterface $tool) {
                return [
                    'type' => 'tool_result',
                    'tool_use_id' => $tool->getCallId(),
                    'content' => $tool->getResult(),
                ];
            }, $message->getTools())
        ];
    }
}
