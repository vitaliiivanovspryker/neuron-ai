<?php

declare(strict_types=1);

namespace NeuronAI\Providers\OpenAI;

use NeuronAI\Chat\Messages\Message;

trait HandleStructured
{
    protected function sanitizeClassName(string $name): string
    {
        // Remove anonymous class markers and special characters
        $name = preg_replace('/class@anonymous.*$/', 'anonymous', $name);
        // Replace any non-alphanumeric characters with underscore
        $name = preg_replace('/[^a-zA-Z0-9_-]/', '_', $name);
        // Ensure it starts with a letter
        if (preg_match('/^[^a-zA-Z]/', $name)) {
            $name = 'class_' . $name;
        }
        return $name;
    }

    public function structured(
        array $messages,
        string $class,
        array $response_format
    ): Message {
        $tk = \explode('\\', $class);
        $className = \end($tk);

        $this->parameters = \array_merge($this->parameters, [
            'response_format' => [
                'type' => 'json_schema',
                'json_schema' => [
                    "name" => $this->sanitizeClassName($className),
                    "strict" => false,
                    "schema" => $response_format,
                ],
            ]
        ]);

        return $this->chat($messages);
    }
}
