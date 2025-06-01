<?php

namespace NeuronAI\Workflow;

use NeuronAI\AgentInterface;
use NeuronAI\Chat\Messages\Message;

interface NodeInterface extends \SplSubject
{
    public function observe(\SplObserver $observer, string $event = "*"): self;
    public function execute(Message|array $messages): Message;
}
