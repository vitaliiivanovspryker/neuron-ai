<?php

namespace NeuronAI;

use NeuronAI\Messages\AbstractMessage;

class InMemoryChatHistory extends AbstractChatHistory
{
    protected array $history = [];

    public function addMessage(AbstractMessage $message): self
    {
        \array_unshift($this->history, $message);
        return $this;
    }

    public function getMessages(): array
    {
        return $this->history;
    }

    public function getLastMessage(): ?AbstractMessage
    {
        return $this->history[0] ?? null;
    }

    public function clear(): self
    {
        $this->history = [];
        return $this;
    }

    public function count(): int
    {
        return count($this->history);
    }

    public function truncate(int $count): self
    {
        $this->history = \array_slice($this->history, $count);
        return $this;
    }
}
