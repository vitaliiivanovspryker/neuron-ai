<?php

namespace NeuronAI\Observability\Events;

class WorkflowNodeStart
{
    /**
     * @param string $node
     */
    public function __construct(
        public string $node,
    ) {
    }
}
