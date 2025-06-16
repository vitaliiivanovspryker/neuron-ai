<?php

namespace NeuronAI\Workflow;

interface PersistenceInterface
{
    public function save(string $workflowId, WorkflowInterrupt $interrupt): void;
    public function load(string $workflowId): ?WorkflowInterrupt;
    public function delete(string $workflowId): void;
}
