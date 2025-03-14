<?php

namespace NeuronAI\Chat\History;

use NeuronAI\Chat\Messages\Message;

class InMemoryChatHistory extends AbstractChatHistory
{
    public function addMessage(Message $message): ChatHistoryInterface
    {
        $this->history[] = $message;

        $this->cutHistoryToContextWindow();

        return $this;
    }

    public function getMessages(): array
    {
        return $this->history;
    }

    public function removeOldestMessage(): ChatHistoryInterface
    {
        \array_unshift($this->history);
        return $this;
    }

    public function clear(): ChatHistoryInterface
    {
        $this->history = [];
        return $this;
    }
}
