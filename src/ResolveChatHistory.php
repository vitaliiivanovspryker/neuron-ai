<?php

namespace NeuronAI;

use NeuronAI\Chat\History\AbstractChatHistory;
use NeuronAI\Chat\History\InMemoryChatHistory;

trait ResolveChatHistory
{
    /**
     * @var AbstractChatHistory
     */
    protected AbstractChatHistory $chatHistory;

    public function withChatHistory(AbstractChatHistory $chatHistory): self
    {
        $this->chatHistory = $chatHistory;
        return $this;
    }

    public function resolveChatHistory(): AbstractChatHistory
    {
        if (!isset($this->chatHistory)) {
            $this->chatHistory = $this->chatHistory();
        }

        return $this->chatHistory;
    }

    public function chatHistory(): AbstractChatHistory
    {
        return new InMemoryChatHistory();
    }
}
