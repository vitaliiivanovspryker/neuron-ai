<?php

declare(strict_types=1);

namespace NeuronAI\Providers\AWSBedrock;

use Aws\Api\Parser\EventParsingIterator;
use Aws\BedrockRuntime\BedrockRuntimeClient;
use Aws\ResultInterface;
use GuzzleHttp\Client;
use GuzzleHttp\Promise\PromiseInterface;
use NeuronAI\Chat\Messages\AssistantMessage;
use NeuronAI\Chat\Messages\Message;
use NeuronAI\Chat\Messages\ToolCallMessage;
use NeuronAI\Providers\AIProviderInterface;
use NeuronAI\Providers\HandleWithTools;
use NeuronAI\Providers\MessageMapperInterface;
use NeuronAI\Tools\ToolInterface;
use NeuronAI\Tools\ToolPropertyInterface;
use NeuronAI\Exceptions\ProviderException;

class BedrockRuntimeAIProvider implements AIProviderInterface
{
    use HandleWithTools;
    use HandleStructured;

    protected ?string $system = null;

    protected BedrockRuntimeClient $bedrockRuntimeClient;

    public function __construct(
        protected MessageMapperInterface $messageMapper,
        protected string $model,
        protected string $region,
        protected string $version = 'latest',
    ) {
        if (!\class_exists('\Aws\BedrockRuntime\BedrockRuntimeClient')) {
            throw new ProviderException('BedrockRuntimeClient is not installed. Please run "composer require aws/aws-sdk-php"');
        }

        $this->bedrockRuntimeClient = new BedrockRuntimeClient([
            'region' => $this->region,
            'version' => $this->version,
        ]);
    }

    public function systemPrompt(?string $prompt): AIProviderInterface
    {
        $this->system = $prompt;

        return $this;
    }

    public function messageMapper(): MessageMapperInterface
    {
        return $this->messageMapper;
    }

    public function chat(array $messages): Message
    {
        return $this->chatAsync($messages)->wait();
    }

    public function chatAsync(array $messages): PromiseInterface
    {
        $payload = $this->createPayLoad($messages);

        return $this->bedrockRuntimeClient
            ->converseAsync($payload)
            ->then(function (ResultInterface $response) {

                $stopReason = $response['stopReason'] ?? '';
                if ($stopReason === 'tool_use') {
                    $tools = [];
                    foreach ($response['output']['message']['content'] ?? [] as $toolContent) {
                        if (isset($toolContent['toolUse'])) {
                            $tools[] = $this->createTool($toolContent);
                        }
                    }

                    return new ToolCallMessage(null, $tools);
                }

                $responseText = '';
                foreach ($response['output']['message']['content'] ?? [] as $content) {
                    if (isset($content['text'])) {
                        $responseText .= $content['text'];
                    }
                }

                return new AssistantMessage($responseText);
            });
    }

    public function stream(array|string $messages, callable $executeToolsCallback): \Generator
    {
        $payload = $this->createPayLoad($messages);
        $result = $this->bedrockRuntimeClient->converseStream($payload);

        $tools = [];
        foreach ($result as $eventParserIterator) {
            if (!$eventParserIterator instanceof EventParsingIterator) {
                continue;
            }

            $toolContent = null;
            foreach ($eventParserIterator as $event) {

                if (isset($event['contentBlockStart']['start']['toolUse'])) {
                    $toolContent = $event['contentBlockStart']['start'];
                    $toolContent['toolUse']['input'] = '';
                    continue;
                }

                if ($toolContent !== null && isset($event['contentBlockDelta']['delta']['toolUse'])) {
                    $toolContent['toolUse']['input'] .= $event['contentBlockDelta']['delta']['toolUse']['input'];
                    continue;
                }

                if (isset($event['contentBlockDelta']['delta']['text'])) {
                    yield $event['contentBlockDelta']['delta']['text'];
                }
            }

            if ($toolContent !== null) {
                $tools[] = $this->createTool($toolContent);
            }
        }

        if (\count($tools) > 0) {
            yield from $executeToolsCallback(
                (new ToolCallMessage(null, $tools))
            );
        }
    }

    protected function createPayLoad(array $messages): array
    {
        $payload = [
            'modelId' => $this->model,
            'messages' => $this->messageMapper()->map($messages),
            'system' => [[
                'text' => $this->system,
            ]],
        ];

        $toolSpecs = $this->generateToolsPayload();

        if (\count($toolSpecs) > 0) {
            $payload['toolConfig']['tools'] = $toolSpecs;
        }

        return $payload;
    }

    protected function generateToolsPayload(): array
    {
        return \array_map(function (ToolInterface $tool): array {
            $payload = [
                'toolSpec' => [
                    'name' => $tool->getName(),
                    'description' => $tool->getDescription(),
                    'inputSchema' => [
                        'json' => [
                            'type' => 'object',
                            'properties' => new \stdClass(),
                            'required' => [],
                        ]
                    ],
                ],
            ];

            $properties = \array_reduce($tool->getProperties(), function (array $carry, ToolPropertyInterface $property): array {
                $carry[$property->getName()] = $property->getJsonSchema();
                return $carry;
            }, []);

            if (!empty($properties)) {
                $payload['toolSpec']['inputSchema']['json'] = [
                    'type' => 'object',
                    'properties' => $properties,
                    'required' => $tool->getRequiredProperties(),
                ];
            }

            return $payload;
        }, $this->tools);
    }

    protected function createTool(array $toolContent): ToolInterface
    {
        $toolUse = $toolContent['toolUse'];
        $tool = $this->findTool($toolUse['name']);
        $tool->setCallId($toolUse['toolUseId']);
        if (\is_string($toolUse['input'])) {
            $toolUse['input'] = \json_decode($toolUse['input'], true);
        }
        $tool->setInputs($toolUse['input'] ?? []);
        return $tool;
    }

    public function setClient(Client $client): AIProviderInterface
    {
        // no need to set client for AWSBedrockAIProvider since it uses its own BedrockRuntimeClient
        return $this;
    }
}
