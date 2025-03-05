<?php

namespace NeuronAI\Chat\History;

use NeuronAI\Chat\Messages\Message;
use NeuronAI\Chat\Messages\Usage;

abstract class AbstractChatHistory implements ChatHistoryInterface
{
    public function __construct(protected int $contextWindow = 50000) {}

    abstract public function addMessage(Message $message): self;

    abstract public function getMessages(): array;

    abstract public function clear(): self;

    public function calculateTotalUsage(): int
    {
        return \array_reduce($this->getMessages(), function (int $carry, Message $message) {
            if ($message->getUsage() instanceof Usage) {
                $carry = $carry + $message->getUsage()->getTotal();
            }

            return $carry;
        }, 0);
    }

    public function toArray(): array
    {
        return \array_map(function (Message $message) {
            return $message->toArray();
        }, $this->getMessages());
    }

    public function jsonSerialize(): array
    {
        return $this->toArray();
    }
}
