<?php

namespace NeuronAI\Observability\Events;

use NeuronAI\Workflow\Workflow;

class WorkflowEnd
{
    public function __construct(protected Workflow $workflow)
    {
    }
}
