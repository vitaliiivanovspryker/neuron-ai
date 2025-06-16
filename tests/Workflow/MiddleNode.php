<?php

namespace NeuronAI\Tests\Workflow;

use NeuronAI\Workflow\NodeInterface;
use NeuronAI\Workflow\WorkflowState;

class MiddleNode implements NodeInterface
{
    public function run(WorkflowState $state): WorkflowState
    {
        $state->set('step', 'middle');
        $state->set('counter', $state->get('counter', 0) + 1);
        return $state;
    }
}
