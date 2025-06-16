<?php

namespace NeuronAI\Tests\Workflow;

use NeuronAI\Workflow\NodeInterface;
use NeuronAI\Workflow\WorkflowState;

class FinishNode implements NodeInterface
{
    public function run(WorkflowState $state): WorkflowState
    {
        $state->set('step', 'end');
        return $state;
    }
}
