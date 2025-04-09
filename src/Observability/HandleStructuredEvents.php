<?php

namespace NeuronAI\Observability;

use NeuronAI\Observability\Events\Deserialized;
use NeuronAI\Observability\Events\Deserializing;
use NeuronAI\Observability\Events\Extracted;
use NeuronAI\Observability\Events\Extracting;
use NeuronAI\Observability\Events\Validated;
use NeuronAI\Observability\Events\Validating;

trait HandleStructuredEvents
{
    protected function extracting(\NeuronAI\AgentInterface $agent, string $event, Extracting $data)
    {

    }

    protected function extracted(\NeuronAI\AgentInterface $agent, string $event, Extracted $data)
    {

    }

    protected function deserializing(\NeuronAI\AgentInterface $agent, string $event, Deserializing $data)
    {

    }

    protected function deserialized(\NeuronAI\AgentInterface $agent, string $event, Deserialized $data)
    {

    }

    protected function validating(\NeuronAI\AgentInterface $agent, string $event, Validating $data)
    {

    }

    protected function validated(\NeuronAI\AgentInterface $agent, string $event, Validated $data)
    {

    }
}
