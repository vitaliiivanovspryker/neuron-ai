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
                'response_schema' => $this->adaptSchema($response_format),
            ]
        ]);

        return $this->chat($messages);
    }

    protected function adaptSchema(array $schema, array $path = []): array
    {
        $result = [];

        foreach ($schema as $key => $value) {
            $currentPath = array_merge($path, [$key]);

            // If the value is still an array after modification, recurse into it
            if (is_array($value)) {
                if (\array_key_exists('additionalProperties', $value)) {
                    unset($value['additionalProperties']);
                }
                $result[$key] = $this->adaptSchema($value, $currentPath);
            } else {
                $result[$key] = $value;
            }
        }

        return $result;
    }
}
