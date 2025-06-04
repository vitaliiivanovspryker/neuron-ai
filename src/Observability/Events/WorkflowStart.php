<?php

namespace NeuronAI\Observability\Events;

use NeuronAI\Workflow\Workflow;

class WorkflowStart
{
    public function __construct(protected Workflow $workflow)
    {
    }
}
