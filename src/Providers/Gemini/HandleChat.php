<?php

namespace NeuronAI\Providers\Gemini;

use GuzzleHttp\Exception\GuzzleException;
use NeuronAI\Chat\Messages\Message;

trait HandleChat
{
    /**
     * Send a message to the LLM.
     *
     * @param Message|array<Message> $messages
     * @throws GuzzleException
     */
    public function chat(array $messages): Message
    {

    }
}
