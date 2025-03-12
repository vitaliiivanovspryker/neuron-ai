<?php

namespace NeuronAI\Chat\History;

use NeuronAI\Chat\Messages\Message;

class InMemoryChatHistory extends AbstractChatHistory
{
    protected array $history = [];

    public function addMessage(Message $message): ChatHistoryInterface
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

    public function clear(): ChatHistoryInterface
    {
        $this->history = [];
        return $this;
    }

    public function truncate(): ChatHistoryInterface
    {
        do {
            \array_shift($this->history);
        } while ($this->contextWindow - $this->calculateTotalUsage() < 0);

        return $this;
    }
}
