<?php

namespace NeuronAI\Workflow;

use NeuronAI\AgentInterface;
use NeuronAI\Chat\Messages\Message;
use NeuronAI\Observability\Observable;
use NeuronAI\StaticConstructor;

class AgentNode implements NodeInterface
{
    use Observable;
    use StaticConstructor;

    public function __construct(
        private readonly AgentInterface $agent,
    ) {
    }

    public function execute(Message|array $messages): Message
    {
        return $this->agent->chat($messages);
    }

    public function observe(\SplObserver $observer, string $event = "*"): static
    {
        $this->agent->observe($observer, $event);
        return $this;
    }
}
