<?php

namespace NeuronAI\Providers\Gemini;

use NeuronAI\Chat\Messages\Message;

trait HandleStructured
{
    public function structured(
        array $messages,
        string $class,
        array $response_format
    ): Message {
        throw new \Exception("Not implemented");
    }
}
