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
        $this->parameters = \array_merge($this->parameters, [
            'generationConfig' => [
                'response_mime_type' => 'application/json',
                'response_schema' => $response_format,
            ]
        ]);

        return $this->chat($messages);
    }
}
