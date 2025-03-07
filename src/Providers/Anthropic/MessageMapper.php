<?php

namespace NeuronAI\Providers\Anthropic;

use NeuronAI\Chat\Messages\Message;
use NeuronAI\Tools\ToolCallMessage;

class MessageMapper
{
    /**
     * Mapped messages.
     *
     * @var array
     */
    protected array $mapping = [];

    /**
     * @param array<Message> $messages
     */
    public function __construct(protected array $messages) {}

    public function map(): array
    {
        foreach ($this->messages as $message) {
            if ($message instanceof ToolCallMessage) {
                $this->mapToolMessage($message);
            }

            $this->mapping[] = $message->jsonSerialize();
        }

        return $this->mapping;
    }

    public function mapToolMessage(ToolCallMessage $message): array
    {
        foreach ($message->getTools() as $tool) {
            $this->mapping[] = [
                'role' => Message::ROLE_USER,
                'content' => [
                    'type' => 'tool_result',
                    'tool_use_id' => $tool->getCallId(),
                    'content' => $tool->getResult(),
                ]
            ];
        }
    }
}
