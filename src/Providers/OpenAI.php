<?php

namespace NeuronAI\Providers;

use GuzzleHttp\Exception\GuzzleException;
use NeuronAI\Chat\Messages\AssistantMessage;
use NeuronAI\Chat\Messages\Message;
use GuzzleHttp\Client;
use NeuronAI\Chat\Messages\Usage;
use NeuronAI\Tools\ToolCallMessage;
use NeuronAI\Tools\ToolInterface;
use NeuronAI\Tools\ToolProperty;

class OpenAI implements AIProviderInterface
{
    use HandleWithTools;

    /**
     * The http client.
     *
     * @var Client
     */
    protected Client $client;

    /**
     * The main URL of the provider API.
     *
     * @var string
     */
    protected string $baseUri = 'https://api.openai.com';

    /**
     * System instructions.
     * https://platform.openai.com/docs/api-reference/chat/create
     *
     * @var ?string
     */
    protected ?string $system;

    public function __construct(
        protected string $key,
        protected string $model,
        protected int $max_tokens = 1024,
    ) {
        $this->client = new Client([
            'base_uri' => $this->baseUri,
            'headers' => [
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
                'Authorization' => 'Bearer ' . $this->key,
            ]
        ]);
    }

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
                    'role' => 'tool',
                    'content' => [
                        'type' => 'tool_result',
                        'tool_call_id' => $tool->getCallId(),
                        'content' => $tool->getResult(),
                    ]
                ];
            }, $messages->getTools());
        } else {
            $messages = \is_array($messages) ? $messages : [$messages];
        }

        // Attach the system prompt
        if (isset($this->system)) {
            \array_unshift($messages, new AssistantMessage($this->system));
        }

        $json = [
            'model' => $this->model,
            'messages' => $messages,
        ];

        // Attach tools
        if (!empty($this->tools)) {
            $json['tools'] = $this->generateToolsPayload();
        }

        $result = $this->client->post('v1/chat/completions', compact('json'))
            ->getBody()->getContents();

        $result = \json_decode($result, true);

        if ($result['status'] === 'requires_action') {
            $response = $this->createToolMessage(
                $result['required_action']['submit_tool_outputs']['tool_calls']
            );
        } else {
            $response = new AssistantMessage($result['choices'][0]['message']['content']);
        }

        if (\array_key_exists('usage', $result)) {
            $response->setUsage(
                new Usage($result['usage']['prompt_tokens'], $result['usage']['completion_tokens'])
            );
        }

        return $response;
    }

    public function generateToolsPayload(): array
    {
        return \array_map(function (ToolInterface $tool) {
            return [
                'type' => 'function',
                'function' => [
                    'name' => $tool->getName(),
                    'description' => $tool->getDescription(),
                    'parameters' => [
                        'type' => 'object',
                        'properties' => \array_reduce($tool->getProperties(), function (array $carry, ToolProperty $property) {
                            $carry[$property->getName()] = [
                                'name' => $property->getName(),
                                'description' => $property->getDescription(),
                            ];

                            return $carry;
                        }, []),
                        'required' => $tool->getRequiredProperties(),
                    ]
                ]
            ];
        }, $this->tools);
    }

    protected function createToolMessage(array $tool_calls): Message
    {
        $tools = \array_map(function (array $item) {
            return $this->findTool($item['function']['name'])
                ->setInputs(json_decode($item['function']['arguments'], true));
        }, $tool_calls);

        return new ToolCallMessage($tools);
    }
}
