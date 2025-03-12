<?php

namespace NeuronAI\Chat\History;

use NeuronAI\Chat\Messages\Message;
use NeuronAI\Exceptions\ChatHistoryException;

class FileChatHistory extends AbstractChatHistory
{
    protected array $history = [];

    public function __construct(
        protected string $directory,
        protected string $key,
        protected int $contextWindow = 50000,
        protected string $prefix = 'neuron_',
        protected string $ext = '.chat'
    ) {
        parent::__construct($this->contextWindow);

        if (!\is_dir($this->directory)) {
            throw new ChatHistoryException("Directory '{$this->directory}' does not exist");
        }

        $this->initHistory();
    }

    protected function initHistory(): void
    {
        if (\is_file($this->getFilePath())) {
            $this->history = json_decode(file_get_contents($this->getFilePath()), true);
        } else {
            $this->history = [];
        }
    }

    protected function getFilePath(): string
    {
        return $this->directory . DIRECTORY_SEPARATOR . $this->prefix.$this->key.$this->ext;
    }

    public function addMessage(Message $message): ChatHistoryInterface
    {
        $this->history[] = $message;
        file_put_contents($this->getFilePath(), json_encode($this->history), LOCK_EX);
        return $this;
    }

    public function getMessages(): array
    {
        return $this->history;
    }

    public function clear(): ChatHistoryInterface
    {
        if (!\unlink($this->getFilePath())) {
            throw new ChatHistoryException("Unable to delete file '{$this->getFilePath()}'");
        }
        $this->history = [];
        return $this;
    }
}
