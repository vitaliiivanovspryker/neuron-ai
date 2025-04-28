<?php

namespace NeuronAI\Providers\Gemini;

use GuzzleHttp\Exception\GuzzleException;
use NeuronAI\Chat\Messages\Message;

trait HandleStructured
{
    /**
     * @throws GuzzleException
     */
    public function structured(
        array $messages,
        string $class,
        array $response_format
    ): Message {
        if (!\array_key_exists('generationConfig', $this->parameters)) {
            $this->parameters['generationConfig'] = [
                'temperature' => 0,
            ];
        }

        $this->parameters['generationConfig'] = \array_merge($this->parameters['generationConfig'], [
            'response_mime_type' => 'application/json',
            'response_schema' => $this->adaptSchema($response_format),
        ]);

        return $this->chat($messages);
    }

    /**
     * Gemini does not support additionalProperties.
     *
     * @param array $schema
     * @return array
     */
    protected function adaptSchema(array $schema): array
    {
        if (\array_key_exists('additionalProperties', $schema)) {
            unset($schema['additionalProperties']);
        }

        foreach ($schema as $key => $value) {
            if (is_array($value)) {
                $schema[$key] = $this->adaptSchema($value);
            }
        }

        return $schema;
    }
}
