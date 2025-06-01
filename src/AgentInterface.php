<?php

namespace NeuronAI;

use NeuronAI\Chat\History\AbstractChatHistory;
use NeuronAI\Chat\Messages\Message;
use NeuronAI\Providers\AIProviderInterface;
use NeuronAI\Tools\ToolInterface;

interface AgentInterface extends \SplSubject
{
    public function withProvider(AIProviderInterface $provider): AgentInterface;

    public function resolveProvider(): AIProviderInterface;

    public function withInstructions(string $instructions): AgentInterface;

    public function instructions(): string;

    public function addTool(ToolInterface $tool): AgentInterface;

    public function getTools(): array;

    public function withChatHistory(AbstractChatHistory $chatHistory): AgentInterface;

    public function resolveChatHistory(): AbstractChatHistory;

    public function observe(\SplObserver $observer, string $event = "*"): \SplObserver;

    public function chat(Message|array $messages): Message;

    public function stream(Message|array $messages): \Generator;

    public function structured(Message|array $messages, ?string $class = null, int $maxRetries = 1): mixed;
}
