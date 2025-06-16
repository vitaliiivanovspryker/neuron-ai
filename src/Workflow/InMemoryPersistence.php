<?php

namespace NeuronAI\Workflow;

class InMemoryPersistence implements PersistenceInterface
{
    private array $storage = [];

    public function save(string $workflowId, WorkflowInterrupt $interrupt): void
    {
        $this->storage[$workflowId] = $interrupt;
    }

    public function load(string $workflowId): ?WorkflowInterrupt
    {
        return $this->storage[$workflowId] ?? null;
    }

    public function delete(string $workflowId): void
    {
        unset($this->storage[$workflowId]);
    }
}
