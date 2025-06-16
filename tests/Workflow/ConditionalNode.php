<?php

namespace NeuronAI\Tests\Workflow;

use NeuronAI\Workflow\NodeInterface;
use NeuronAI\Workflow\WorkflowState;

class ConditionalNode implements NodeInterface
{
    public function run(WorkflowState $state): WorkflowState
    {
        $counter = $state->get('counter', 0);
        $state->set('should_loop', $counter < 3);
        return $state;
    }
}
