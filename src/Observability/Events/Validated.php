<?php

namespace NeuronAI\Observability\Events;

class Validated
{
    public function __construct(public string $class, public string $json) {}
}
