<?php

namespace NeuronAI\Chat\History;

use NeuronAI\Chat\Messages\Message;

class InMemoryChatHistory extends AbstractChatHistory
{
    public function addMessage(Message $message): ChatHistoryInterface
    {
        $this->history[] = $message;

        $this->cutToContextWindow();

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
}
