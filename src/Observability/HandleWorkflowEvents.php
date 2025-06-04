<?php

namespace NeuronAI\Observability;

use NeuronAI\Observability\Events\WorkflowEnd;
use NeuronAI\Observability\Events\WorkflowStart;

trait HandleWorkflowEvents
{
    public function workflowStart(\SplObserver $agent, string $event, WorkflowStart $data)
    {

    }

    public function workflowEnd(\SplObserver $agent, string $event, WorkflowEnd $data)
    {

    }
}
