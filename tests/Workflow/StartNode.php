<?php

namespace NeuronAI\Tests\Workflow;

use NeuronAI\Workflow\NodeInterface;
use NeuronAI\Workflow\WorkflowState;

class StartNode implements NodeInterface
{
    public function run(WorkflowState $state): WorkflowState
    {
        $state->set('step', 'start');
        return $state;
    }
}
