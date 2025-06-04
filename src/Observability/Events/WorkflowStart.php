<?php

namespace NeuronAI\Observability\Events;

class WorkflowStart
{
    public function __construct(protected array $executionList)
    {
    }
}
