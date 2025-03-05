<?php

namespace NeuronAI;

use NeuronAI\Chat\Messages\Message;
use NeuronAI\Providers\AIProviderInterface;
use NeuronAI\Tools\ToolInterface;

interface AgentInterface extends \SplSubject
{
    public function provider(): AIProviderInterface;

    public function setProvider(AIProviderInterface $provider): self;

    public function instructions(): ?string;

    public function setInstructions(?string $instructions): self;

    public function tools(): array;

    public function addTool(ToolInterface $tool): self;

    public function chatHistory(): AbstractChatHistory;

    public function withChatHistory(AbstractChatHistory $chatHistory): self;

    public function observe(\SplObserver $observer, string $event = "*"): self;

    public function chat(Message $message): Message;
}
