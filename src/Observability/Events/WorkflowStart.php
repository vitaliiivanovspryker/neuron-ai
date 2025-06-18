<?php

namespace NeuronAI\Observability\Events;

use NeuronAI\Workflow\Edge;
use NeuronAI\Workflow\Node;

class WorkflowStart
{
    /**
     * @param Node[] $nodes
     * @param Edge[] $edges
     */
    public function __construct(public array $nodes, public array $edges)
    {
    }
}
