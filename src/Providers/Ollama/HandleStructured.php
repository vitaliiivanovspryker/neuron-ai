<?php

namespace NeuronAI\Providers\Ollama;

use NeuronAI\Chat\Messages\Message;
use NeuronAI\Exceptions\ProviderException;

trait HandleStructured
{
    /**
     * @param array<Message> $messages
     * @param array $response_format
     * @return Message
     * @throws ProviderException
     */
    public function structured(
        array $messages,
        string $class,
        array $response_format
    ): Message {
        $this->parameters = \array_merge($this->parameters, [
            'format' => $response_format,
        ]);

        return $this->chat($messages);
    }
}
