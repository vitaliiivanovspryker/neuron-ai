<?php

namespace NeuronAI\Providers;

use GuzzleHttp\RequestOptions;
use NeuronAI\Messages\AssistantMessage;
use NeuronAI\Messages\Message;
use GuzzleHttp\Client;

class Mistral implements AIProviderInterface
{
    /**
     * The http client.
     *
     * @var Client
     */
    protected Client $client;

    /**
     * System instructions.
     * https://docs.mistral.ai/capabilities/completion/#chat-messages
     *
     * @var string
     */
    protected string $system;

    public function __construct(
        protected string $key,
        protected string $model,
        protected int $max_tokens = 1024
    ) {
        $this->client = new Client([
            'base_uri' => 'https://api.mistral.ai/v1',
            'headers' => [
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
                'Authorization' => "Bearer {$this->key}",
            ]
        ]);
    }

    public function systemPrompt(string $prompt): AIProviderInterface
    {
        $this->system = $prompt;
        return $this;
    }

    public function chat(array|string $prompt): Message
    {
        if (\is_string($prompt)) {
            $prompt = [['role' => 'user', 'content' => $prompt]];
        }

        if (isset($this->system)) {
            \array_unshift($prompt, ['role' => 'system', 'content' => $this->system]);
        }

        $result = $this->client->post('/chat/completions', [
            RequestOptions::JSON => [
                'model' => $this->model,
                'messages' => $prompt,
            ]
        ])->getBody()->getContents();

        $result = \json_decode($result, true);

        // todo: attach usage to the response message

        // todo: Add tool call management

        return new AssistantMessage($result['choices'][0]['message']['content']);
    }

    public function setTools(array $tools): AIProviderInterface
    {
        throw new \LogicException('Not implemented');
    }
}
