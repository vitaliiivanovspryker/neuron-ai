<?php

namespace NeuronAI\Chat;

use NeuronAI\AbstractChatHistory;
use NeuronAI\Chat\Messages\Message;

class InMemoryChatHistory extends AbstractChatHistory
{
    protected array $history = [];

    public function addMessage(Message $message): self
    {
        $this->history[] =$message;
        return $this;
    }

    public function getMessages(): array
    {
        return $this->history;
    }

    public function getLastMessage(): ?Message
    {
        return $this->history[
            max(count($this->history) - 1, 0)
        ] ?? null;
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
