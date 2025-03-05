<?php

namespace NeuronAI\Providers;

use GuzzleHttp\Exception\GuzzleException;
use NeuronAI\Chat\Messages\AssistantMessage;
use NeuronAI\Chat\Messages\Message;
use NeuronAI\Tools\ToolInterface;
use NeuronAI\Tools\ToolCallMessage;
use NeuronAI\Tools\ToolProperty;
use NeuronAI\Chat\Messages\Usage;
use GuzzleHttp\Client;

class Anthropic implements AIProviderInterface
{
    use HandleWithTools;

    /**
     * The http client.
     *
     * @var Client
     */
    protected Client $client;

    /**
     * System instructions.
     * https://docs.anthropic.com/claude/docs/system-prompts#how-to-use-system-prompts
     *
     * @var ?string
     */
    protected ?string $system;

    /**
     * AnthropicClaude constructor.
     */
    public function __construct(
        protected string $key,
        protected string $model,
        protected string $version = '2023-06-01',
        protected int $max_tokens = 8192,
        protected ?float $temperature = null,
        protected ?array $stop_sequences = null,
    ) {
        $this->client = new Client([
            'base_uri' => 'https://api.anthropic.com',
            'headers' => [
                'Content-Type' => 'application/json',
                'x-api-key' => $this->key,
                'anthropic-version' => $version,
            ]
        ]);
    }

    /**
     * @inerhitDoc
     */
    public function systemPrompt(?string $prompt): AIProviderInterface
    {
        $this->system = $prompt;
        return $this;
    }

    /**
     * Send a message to the LLM.
     *
     * @param Message|array<Message> $messages
     * @throws GuzzleException
     */
    public function chat(Message|array $messages): Message
    {
        if ($messages instanceof ToolCallMessage) {
            $messages = \array_map(function (ToolInterface $tool) {
                return [
                    'role' => Message::ROLE_USER,
                    'content' => [
                        'type' => 'tool_result',
                        'tool_use_id' => $tool->getCallId(),
                        'content' => $tool->getResult(),
                    ]
                ];
            }, $messages->getTools());
        }

        $json = \array_filter([
            'model' => $this->model,
            'max_tokens' => $this->max_tokens,
            'stop_sequences' => $this->stop_sequences,
            'temperature' => $this->temperature,
            'system' => $this->system ?? null,
            'messages' => \is_array($messages) ? $messages : [$messages],
        ]);

        if (!empty($this->tools)) {
            $json['tools'] = $this->generateToolsPayload();
        }

        // https://docs.anthropic.com/claude/reference/messages_post
        $result = $this->client->post('v1/messages', compact('json'))
            ->getBody()->getContents();

        $result = \json_decode($result, true);

        $content = \end($result['content']);

        if ($content['type'] === 'tool_use') {
            $response = $this->createToolMessage($content);
        } else {
            $response = new AssistantMessage($content['text']);
        }

        // Attach the usage for the current interaction
        if (\array_key_exists('usage', $result)) {
            $response->setUsage(
                new Usage(
                    $result['usage']['input_tokens'],
                    $result['usage']['output_tokens']
                )
            );
        }

        return $response;
    }

    public function generateToolsPayload(): array
    {
        return \array_map(function (ToolInterface $tool) {
            return [
                'name' => $tool->getName(),
                'description' => $tool->getDescription(),
                'input_schema' => [
                    'type' => 'object',
                    'properties' => \array_reduce($tool->getProperties(), function ($carry, ToolProperty $property) {
                        $carry[$property->getName()] = [
                            'type' => $property->getType(),
                            'description' => $property->getDescription(),
                        ];

                        return $carry;
                    }, []),
                    'required' => $tool->getRequiredProperties(),
                ],
            ];
        }, $this->tools);
    }

    public function createToolMessage(array $content): Message
    {
        // Anthropic call one tool at a time. So we pass an array with one element.
        return new ToolCallMessage([
            $this->findTool($content['name'])
                ->setInputs($content['input'])
                ->setCallId($content['id'])
        ]);
    }
}
