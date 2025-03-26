<?php

namespace NeuronAI\Providers\Ollama;

use NeuronAI\Exceptions\ProviderException;

trait HandleStream
{
    public function stream(array|string $messages, callable $executeToolsCallback): \Generator
    {
        throw new ProviderException("Ollama provider does not support stream response yet.");
    }
}
