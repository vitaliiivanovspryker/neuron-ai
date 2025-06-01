<?php

namespace NeuronAI\Workflow;

use NeuronAI\AgentInterface;
use NeuronAI\Chat\Messages\Message;

interface NodeInterface extends \SplSubject
{
    public function observe(\SplObserver $observer, string $event = "*"): AgentInterface;
    public function execute(Message|array $messages): Message;
}
