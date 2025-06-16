<?php

namespace NeuronAI\Workflow;

class Edge
{
    public function __construct(
        protected string $from,
        protected string $to,
        private ?\Closure $condition = null
    ) {
    }

    public function getFrom(): string
    {
        return $this->from;
    }

    public function getTo(): string
    {
        return $this->to;
    }

    public function shouldExecute(WorkflowState $state): bool
    {
        if ($this->condition === null) {
            return true;
        }

        return ($this->condition)($state);
    }
}
