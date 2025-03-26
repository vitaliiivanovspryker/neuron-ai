<?php

namespace NeuronAI\Providers\Ollama;

use NeuronAI\Chat\Messages\AssistantMessage;
use NeuronAI\Chat\Messages\Message;
use NeuronAI\Chat\Messages\Usage;
use NeuronAI\Exceptions\ProviderException;

trait HandleChat
{
    public function chat(array $messages): Message
    {
        // Include the system prompt
        if (isset($this->system)) {
            \array_unshift($messages, new Message(Message::ROLE_SYSTEM, $this->system));
        }

        $mapper = new MessageMapper($messages);

        $json = \array_filter([
            'stream' => false,
            'model' => $this->model,
            'temperature' => $this->temperature,
            'messages' => $mapper->map(),
        ]);

        if (!empty($this->tools)) {
            $json['tools'] = $this->generateToolsPayload();
        }

        $response = $this->client->post('generate', compact('json'));

        if ($response->getStatusCode() < 200 || $response->getStatusCode() >= 300) {
            throw new ProviderException("Ollama chat error: {$response->getBody()->getContents()}");
        }

        $response = json_decode($response->getBody()->getContents(), true);
        $response = $response['message'];

        if (\array_key_exists('tool_calls', $response)) {
            $message = $this->createToolMessage($response);
        } else {
            $message = new AssistantMessage($response['content']);
        }

        if (\array_key_exists('prompt_eval_count', $response) && \array_key_exists('eval_count', $response)) {
            $message->setUsage(
                new Usage($response['prompt_eval_count'], $response['eval_count'])
            );
        }

        return $message;
    }
}
