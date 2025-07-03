<?php

namespace NeuronAI\Observability\Events;

class SchemaGeneration
{
    public function __construct(public string $class)
    {
    }
}
