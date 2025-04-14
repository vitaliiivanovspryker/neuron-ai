<?php

namespace NeuronAI;

use NeuronAI\Chat\History\AbstractChatHistory;
use NeuronAI\Chat\Messages\Message;
use NeuronAI\Providers\AIProviderInterface;
use NeuronAI\Tools\ToolInterface;

interface AgentInterface extends \SplSubject
{
    public function setProvider(AIProviderInterface $provider): AgentInterface;

    public function setInstructions(string $instructions): AgentInterface;

    public function addTool(ToolInterface $tool): AgentInterface;

    public function resolveChatHistory(): AbstractChatHistory;

    public function withChatHistory(AbstractChatHistory $chatHistory): AgentInterface;

    public function observe(\SplObserver $observer, string $event = "*"): AgentInterface;

    public function chat(Message|array $messages): Message;

    public function stream(Message|array $messages): \Generator;

    public function structured(Message|array $messages, ?string $class = null, int $maxRetries): mixed;
}
