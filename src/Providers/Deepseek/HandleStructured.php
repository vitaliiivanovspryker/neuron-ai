<?php

declare(strict_types=1);

namespace NeuronAI\Providers\Deepseek;

use NeuronAI\Chat\Messages\Message;

trait HandleStructured
{
    public function structured(
        array $messages,
        string $class,
        array $response_format
    ): Message {
        $this->parameters = \array_merge($this->parameters, [
            'response_format' => [
                'type' => 'json_object',
            ]
        ]);

        $this->system .= \PHP_EOL."# OUTPUT CONSTRAINTS".\PHP_EOL
            .'Generate a json respecting this schema: '.\json_encode($response_format);

        return $this->chat($messages);
    }
}
