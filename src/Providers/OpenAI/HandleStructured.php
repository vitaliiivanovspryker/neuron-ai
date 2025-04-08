<?php

namespace NeuronAI\Providers\OpenAI;

use NeuronAI\Chat\Messages\Message;

trait HandleStructured
{
    /**
     * @param array<Message> $messages
     * @param array $response_format
     * @return Message
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function structured(array $messages, array $response_format): Message
    {
        $this->parameters = \array_merge($this->parameters, [
            'response_format' => [
                'type' => 'json_schema',
                'json_schema' => $response_format,
            ]
        ]);

        return $this->chat($messages);
    }
}
