<?php

namespace NeuronAI\Providers\OpenAI;

use GuzzleHttp\Client;
use NeuronAI\Chat\Messages\Message;
use NeuronAI\Chat\Messages\ToolCallMessage;
use NeuronAI\HasGuzzleClient;
use NeuronAI\Providers\AIProviderInterface;
use NeuronAI\Providers\HandleWithTools;
use NeuronAI\Providers\MessageMapperInterface;
use NeuronAI\Tools\ArrayProperty;
use NeuronAI\Tools\ObjectProperty;
use NeuronAI\Tools\ToolProperty;
use NeuronAI\Tools\ToolInterface;
use NeuronAI\Tools\ToolPropertyInterface;

class OpenAI implements AIProviderInterface
{
    use HasGuzzleClient;
    use HandleWithTools;
    use HandleChat;
    use HandleStream;
    use HandleStructured;

    /**
     * The main URL of the provider API.
     *
     * @var string
     */
    protected string $baseUri = 'https://api.openai.com/v1';

    /**
     * System instructions.
     * https://platform.openai.com/docs/api-reference/chat/create
     *
     * @var ?string
     */
    protected ?string $system = null;

    /**
     * The component responsible for mapping the NeuronAI Message to the AI provider format.
     *
     * @var MessageMapperInterface
     */
    protected MessageMapperInterface $messageMapper;

    public function __construct(
        protected string $key,
        protected string $model,
        protected array $parameters = [],
    ) {
        $this->client = new Client([
            'base_uri' => trim($this->baseUri, '/').'/',
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

    public function messageMapper(): MessageMapperInterface
    {
        if (!isset($this->messageMapper)) {
            $this->messageMapper = new MessageMapper();
        }
        return $this->messageMapper;
    }

    public function generateToolsPayload(): array
    {
        return \array_map(function (ToolInterface $tool) {
            $payload = [
                'type' => 'function',
                'function' => [
                    'name' => $tool->getName(),
                    'description' => $tool->getDescription(),
                    'parameters' => [
                        'type' => 'object',
                        'properties' => new \stdClass(),
                        'required' => [],
                    ],
                ]
            ];

            $properties = \array_reduce($tool->getProperties(), function (array $carry, ToolPropertyInterface $property) {
                $carry[$property->getName()] = [
                    'description' => $property->getDescription(),
                    'type' => $property->getType()->value,
                ];

                if ($property instanceof ToolProperty && !empty($property->getEnum())) {
                    $carry[$property->getName()]['enum'] = $property->getEnum();
                }

                if ($property instanceof ArrayProperty && !empty($property->getItems())) {
                    $carry[$property->getName()]['items'] = [
                        'type' => 'object',
                        'properties' =>  $property->makeItemsSchema(),
                        'required' => $property->getRequiredProperties(),
                    ];
                }

                if ($property instanceof ObjectProperty && !empty($property->getItems())) {
                    $carry[$property->getName()]['properties'] = $property->makeItemsSchema();
                    $carry[$property->getName()]['required'] = $property->getRequiredProperties();
                }

                return $carry;
            }, []);

            if (!empty($properties)) {
                $payload['function']['parameters'] = [
                    'type' => 'object',
                    'properties' => $properties,
                    'required' => $tool->getRequiredProperties(),
                ];
            }

            return $payload;
        }, $this->tools);
    }

    protected function createToolCallMessage(array $message): Message
    {
        $tools = \array_map(
            fn (array $item) => $this->findTool($item['function']['name'])
                ->setInputs(
                    \json_decode($item['function']['arguments'], true)
                )
                ->setCallId($item['id']),
            $message['tool_calls']
        );

        $result = new ToolCallMessage(
            $message['content'],
            $tools
        );

        return $result->addMetadata('tool_calls', $message['tool_calls']);
    }
}
