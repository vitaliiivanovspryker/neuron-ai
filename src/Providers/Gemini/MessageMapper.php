<?php

namespace NeuronAI\Providers\Gemini;

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
    protected array $mapping = [];

    public function map(array $messages): array
    {
        foreach ($messages as $message) {
            match ($message::class) {
                Message::class,
                UserMessage::class,
                AssistantMessage::class => $this->mapMessage($message),
                ToolCallMessage::class => $this->mapToolCall($message),
                ToolCallResultMessage::class => $this->mapToolsResult($message),
                default => throw new AgentException('Could not map message type '.$message::class),
            };
        }

        return $this->mapping;
    }

    protected function mapMessage(Message $message): void
    {
        $this->mapping[] = [
            'role' => $message->getRole(),
            'parts' => [
                ['text' => $message->getContent()]
            ],
        ];
    }

    protected function mapToolCall(ToolCallMessage $message): void
    {
        $this->mapping[] = [
            'role' => Message::ROLE_MODEL,
            'parts' => $message->getContent(),
        ];
    }

    protected function mapToolsResult(ToolCallResultMessage $message): void
    {
        $this->mapping[] = [
            'role' => Message::ROLE_USER,
            'parts' => \array_map(function (ToolInterface $tool) {
                return [
                    'functionResponse' => [
                        'name' => $tool->getName(),
                        'response' => [
                            'name' => $tool->getName(),
                            'content' => $tool->getResult(),
                        ],
                    ],
                ];
            }, $message->getTools()),
        ];
    }
}
