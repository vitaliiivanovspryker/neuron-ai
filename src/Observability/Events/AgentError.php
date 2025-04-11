<?php

namespace NeuronAI\Observability\Events;

class AgentError
{
    public function __construct(public \Throwable $exception) {}
}
