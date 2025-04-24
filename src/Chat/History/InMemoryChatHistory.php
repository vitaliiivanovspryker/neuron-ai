<?php

namespace NeuronAI\Chat\History;

use NeuronAI\Chat\Messages\Message;

class InMemoryChatHistory extends AbstractChatHistory
{
    public function __construct(int $contextWindow = 50000)
    {
        parent::__construct($contextWindow);
    }

    public function removeOldestMessage(): ChatHistoryInterface
    {
        return $this;
    }

    protected function clear(): ChatHistoryInterface
    {
        return $this;
    }

    protected function storeMessage(array $message): ChatHistoryInterface
    {
        return $this;
    }
}
