<?php

namespace NeuronAI;

use NeuronAI\Messages\Message;
use NeuronAI\Providers\AIProviderInterface;
use NeuronAI\Tools\ToolInterface;

interface AgentInterface extends \SplSubject
{
    public function provider(): AIProviderInterface;

    public function setProvider(AIProviderInterface $provider): self;

    public function instructions(): ?string;

    public function setInstructions(?string $instructions): self;

    public function addMessage(string|Message $message): self;

    public function withMessages(array $messages): self;

    public function tools(): array;

    public function addTool(ToolInterface $tool): self;

    public function resolveChatHistory(): AbstractChatHistory;

    public function withChatHistory(AbstractChatHistory $chatHistory): self;

    public function observe(\SplObserver $observer, string $event = "*"): self;

    public function run(?Message $message = null): Message;
}
