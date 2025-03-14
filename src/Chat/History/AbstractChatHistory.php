<?php

namespace NeuronAI\Chat\History;

use NeuronAI\Chat\Messages\Message;
use NeuronAI\Chat\Messages\Usage;

abstract class AbstractChatHistory implements ChatHistoryInterface
{
    protected array $history = [];

    public function __construct(protected int $contextWindow = 50000) {}

    abstract public function addMessage(Message $message): ChatHistoryInterface;

    abstract public function getMessages(): array;

    abstract public function removeOldestMessage(): ChatHistoryInterface;

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

    public function cutHistoryToContextWindow(): ChatHistoryInterface
    {
        $freeMemory = $this->contextWindow - $this->calculateTotalUsage();

        if ($freeMemory > 0) {
            return $this;
        }

        // Cut old messages
        do {
            $this->removeOldestMessage();
        } while ($this->contextWindow - $this->calculateTotalUsage() < 0);

        return $this;
    }

    public function jsonSerialize(): array
    {
        return \array_map(function (Message $message) {
            return $message->jsonSerialize();
        }, $this->getMessages());
    }

    protected function unserializeMessages(array $messages): array
    {
        return \array_map(function (array $message) {
            return $this->unserializeMessage($message);
        }, $messages);
    }

    protected function unserializeMessage(array $message): Message
    {
        $item = new Message($message['role'], $message['content']??'');

        foreach ($message as $key => $value) {
            if ($key === 'role' || $key === 'content') {
                continue;
            }
            if ($key === 'usage') {
                $item->setUsage(
                    new Usage($message['usage']['input_tokens'], $message['usage']['output_tokens'])
                );
                continue;
            }
            $item->addMetadata($key, $value);
        }

        return $item;
    }
}
