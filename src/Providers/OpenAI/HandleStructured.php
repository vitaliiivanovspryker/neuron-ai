<?php

namespace NeuronAI\Providers\OpenAI;

use GuzzleHttp\Exception\GuzzleException;
use NeuronAI\Chat\Messages\Message;

trait HandleStructured
{
    /**
     * @param array<Message> $messages
     * @param string $class
     * @param array $response_format
     * @return Message
     * @throws GuzzleException
     */
    public function structured(
        array $messages,
        string $class,
        array $response_format
    ): Message {
        $this->parameters = \array_merge($this->parameters, [
            'response_format' => [
                'type' => 'json_schema',
                'json_schema' => [
                    "name" => $class,
                    "strict" => true,
                    "schema" => $response_format,
                ],
            ]
        ]);

        return $this->chat($messages);
    }
}
