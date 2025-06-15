<?php

namespace NeuronAI\Workflow;

interface NodeInterface
{
    public function run(WorkflowState $state): WorkflowState;
}
