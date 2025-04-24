<?php

namespace NeuronAI\Providers\Anthropic;

use NeuronAI\Chat\Messages\Message;
use NeuronAI\Chat\Messages\ToolCallResultMessage;
use NeuronAI\Providers\MessageMapperInterface;
use NeuronAI\Tools\ToolInterface;

class MessageMapper implements MessageMapperInterface
{
    public function map(Message $message): array
    {
        if ($message instanceof ToolCallResultMessage) {
            return $this->mapToolsResult($message->getTools());
        } else {
            return $this->mapMessage($message);
        }
    }

    public function mapMessage(Message $message): array
    {
        $message = $message->jsonSerialize();
        unset($message['usage']);
        return $message;
    }

    public function mapToolsResult(array $tools): array
    {
        return [
            'role' => Message::ROLE_USER,
            'content' => \array_map(function (ToolInterface $tool) {
                return [
                    'type' => 'tool_result',
                    'tool_use_id' => $tool->getCallId(),
                    'content' => $tool->getResult(),
                ];
            }, $tools)
        ];
    }
}
