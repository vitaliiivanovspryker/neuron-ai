<?php

namespace NeuronAI\Providers;

use NeuronAI\Messages\Message;
use NeuronAI\Tools\Tool;
use NeuronAI\Tools\ToolInterface;
use NeuronAI\Tools\ToolCallMessage;
use NeuronAI\Tools\ToolProperty;
use NeuronAI\Messages\Usage;
use GuzzleHttp\Client;

class Anthropic implements AIProviderInterface
{
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
     * @var string
     */
    protected string $system;

    /**
     * https://docs.anthropic.com/en/docs/build-with-claude/tool-use/overview
     *
     * @var array<Tool>
     */
    protected array $tools;

    /**
     * AnthropicClaude constructor.
     */
    public function __construct(
        protected string $key,
        protected string $model,
        protected string $version,
        protected int $max_tokens,
        protected int $context_window,
        protected ?float $temperature = null,
        protected ?array $stop_sequences = null,
    ) {
        $this->client = new Client([
            'base_uri' => 'https://api.anthropic.com/v1',
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
    public function systemPrompt(string $prompt): AIProviderInterface
    {
        $this->system = $prompt;
        return $this;
    }

    /**
     * Send a prompt to the AI agent.
     *
     * @param array|string $prompt
     * @return Message
     * @throws \Exception
     */
    public function chat(array|string $prompt): Message
    {
        if (\is_string($prompt)) {
            $prompt = [['role' => 'user', 'content' => $prompt]];
        }

        $json = \array_filter([
            'model' => $this->model,
            'max_tokens' => $this->max_tokens,
            'stop_sequences' => $this->stop_sequences,
            'temperature' => $this->temperature,
            'system' => $this->system ?? null,
            'messages' => $prompt,
        ]);

        if (!empty($this->tools)) {
            $json['tools'] = $this->generateToolsPayload();
        }

        // https://docs.anthropic.com/claude/reference/messages_post
        $result = $this->client->post('/messages', compact('json'))
            ->getBody()->getContents();

        $result = \json_decode($result, true);

        $content = \last($result['content']);

        // Identify the right message to use
        if ($content['type'] === 'tool_use') {
            $response = new ToolCallMessage(
                $this->findTool($content['name']),
                $content['input']
            );
        } else {
            $response = new Message('assistant', \last($result['content'])['text']);
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

    /**
     * The context window limitation of the LLM.
     *
     * @return int
     */
    public function contextWindow(): int
    {
        return $this->context_window;
    }

    /**
     * The maximum number of tokens to generate before stopping.
     *
     * @return int
     */
    public function maxCompletionTokens(): int
    {
        return  $this->max_tokens;
    }

    public function setTools(array $tools): self
    {
        $this->tools = $tools;
        return $this;
    }

    public function findTool($name): ?Tool
    {
        foreach ($this->tools as $tool) {
            if ($tool->getName() === $name) {
                return $tool;
            }
        }

        return null;
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
}
