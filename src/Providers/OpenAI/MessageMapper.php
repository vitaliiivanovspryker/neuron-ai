<?php

namespace NeuronAI\Providers\OpenAI;

use NeuronAI\Chat\Messages\Message;
use NeuronAI\Chat\Messages\ToolCallMessage;
use NeuronAI\Chat\Messages\ToolCallResultMessage;

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
            $this->mapping[] = $message->jsonSerialize();

            if ($message instanceof ToolCallResultMessage) {
                $this->addToolsResult($message->getTools());
            }
        }

        return $this->mapping;
    }

    public function addToolsResult(array $tools): void
    {
        foreach ($tools as $tool) {
            $this->mapping[] = [
                'role' => 'tool',
                'tool_call_id' => $tool->getCallId(),
                'content' => $tool->getResult()
            ];
        }
    }
}
