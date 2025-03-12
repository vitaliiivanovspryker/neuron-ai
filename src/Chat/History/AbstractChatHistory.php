<?php

namespace NeuronAI\Chat\History;

use NeuronAI\Chat\Messages\Message;
use NeuronAI\Chat\Messages\MessageMapperInterface;
use NeuronAI\Chat\Messages\Usage;

abstract class AbstractChatHistory implements ChatHistoryInterface
{
    public function __construct(protected int $contextWindow = 50000) {}

    abstract public function addMessage(Message $message): ChatHistoryInterface;

    abstract public function getMessages(): array;

    abstract public function clear(): ChatHistoryInterface;

    public function calculateTotalUsage(): int
    {
        return \array_reduce($this->getMessages(), function (int $carry, Message $message) {
            if ($message->getUsage() instanceof Usage) {
                $carry = $carry + $message->getUsage()->getTotal();
            }

            return $carry;
        }, 0);
    }

    public function jsonSerialize(): array
    {
        return \array_map(function (Message $message) {
            return $message->jsonSerialize();
        }, $this->getMessages());
    }
}
