<?php

namespace NeuronAI\Workflow;

use NeuronAI\Chat\Messages\Message;

interface NodeInterface extends \SplSubject
{
    public function observe(\SplObserver $observer, string $event = "*"): static;
    public function execute(Message|array $messages): Message;
}
