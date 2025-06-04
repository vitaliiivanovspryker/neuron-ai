<?php

namespace NeuronAI\Observability\Events;

use NeuronAI\Chat\Messages\Message;
use NeuronAI\Workflow\Workflow;

class WorkflowEnd
{
    public function __construct(Message $lastReply)
    {
    }
}
