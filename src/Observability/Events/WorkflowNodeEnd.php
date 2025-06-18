<?php

namespace NeuronAI\Observability\Events;

class WorkflowNodeEnd
{
    public function __construct(
        public string $node,
    ) {
    }
}
