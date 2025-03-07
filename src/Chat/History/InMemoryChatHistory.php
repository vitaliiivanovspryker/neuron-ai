<?php

namespace NeuronAI\Chat\History;

use NeuronAI\Chat\Messages\Message;
use NeuronAI\Chat\Messages\MessageMapperInterface;

class InMemoryChatHistory extends AbstractChatHistory
{
    protected array $history = [];

    protected MessageMapperInterface $mapper;

    public function addMessage(Message $message): self
    {
        $this->history[] = $message;

        $freeMemory = $this->contextWindow - $this->calculateTotalUsage();

        if ($freeMemory < 0) {
            $this->truncate();
        }

        return $this;
    }

    public function getMessages(): array
    {
        return $this->history;
    }

    public function clear(): self
    {
        $this->history = [];
        return $this;
    }

    public function truncate(): self
    {
        do {
            \array_pop($this->history);
        } while ($this->contextWindow - $this->calculateTotalUsage() < 0);

        return $this;
    }
}
