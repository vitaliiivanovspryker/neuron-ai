<?php

namespace NeuronAI\Chat\History;

use NeuronAI\Chat\Messages\Message;

class InMemoryChatHistory extends AbstractChatHistory
{
    public function removeOldestMessage(): ChatHistoryInterface
    {
        return $this;
    }

    public function clear(): ChatHistoryInterface
    {
        $this->history = [];
        return $this;
    }

    protected function storeMessage(Message $message): ChatHistoryInterface
    {
        // nothing to do for in-memory
        return  $this;
    }
}
