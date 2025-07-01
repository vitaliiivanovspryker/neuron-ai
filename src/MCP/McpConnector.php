<?php

declare(strict_types=1);

namespace NeuronAI\MCP;

use NeuronAI\Exceptions\ArrayPropertyException;
use NeuronAI\StaticConstructor;
use NeuronAI\Tools\ArrayProperty;
use NeuronAI\Tools\ObjectProperty;
use NeuronAI\Tools\PropertyType;
use NeuronAI\Tools\ToolProperty;
use NeuronAI\Tools\Tool;
use NeuronAI\Tools\ToolInterface;

class McpConnector
{
    use StaticConstructor;

    protected McpClient $client;

    public function __construct(array $config)
    {
        $this->client = new McpClient($config);
    }

    /**
     * Get the list of available Tools from the server.
     *
     * @return ToolInterface[]
     * @throws \Exception
     */
    public function tools(): array
    {
        $tools = $this->client->listTools();

        return \array_map(fn ($tool) => $this->createTool($tool), $tools);
    }

    /**
     * Convert the list of tools from the MCP server to Neuron compatible entities.
     * @throws ArrayPropertyException
     * @throws \ReflectionException
     */
    protected function createTool(array $item): ToolInterface
    {
        $tool = Tool::make(
            name: $item['name'],
            description: $item['description'] ?? ''
        )->setCallable(function (...$arguments) use ($item) {
            $response = \call_user_func($this->client->callTool(...), $item['name'], $arguments);

            if (\array_key_exists('error', $response)) {
                throw new McpException($response['error']['message']);
            }

            $response = $response['result']['content'][0];

            if ($response['type'] === 'text') {
                return $response['text'];
            }

            if ($response['type'] === 'image') {
                return $response;
            }

            throw new McpException("Tool response format not supported: {$response['type']}");
        });

        foreach ($item['inputSchema']['properties'] as $name => $input) {
            $required = \in_array($name, $item['inputSchema']['required'] ?? []);
            $types = \is_array($input['type']) ? $input['type'] : [$input['type']];

            foreach ($types as $type) {
                try {
                    $type = PropertyType::from($type);
                    break;
                } catch (\Throwable) {
                }
            }

            $type = $type ?? PropertyType::STRING;

            $property = match ($type) {
                PropertyType::ARRAY => $this->createArrayProperty($name, $type, $required, $input),
                PropertyType::OBJECT => $this->createObjectProperty($name, $type, $required, $input),
                default => $this->createProperty($name, $type, $required, $input),
            };

            $tool->addProperty($property);
        }

        return $tool;
    }

    protected function createProperty(string $name, PropertyType $type, bool $required, array $input): ToolProperty
    {
        return new ToolProperty(
            name: $name,
            type: $type,
            description: $input['description'] ?? null,
            required: $required,
            enum: $input['items']['enum'] ?? []
        );
    }

    /**
     * @throws ArrayPropertyException
     */
    protected function createArrayProperty(string $name, PropertyType $type, bool $required, array $input): ArrayProperty
    {
        return new ArrayProperty(
            name: $name,
            description: $input['description'] ?? null,
            required: $required,
            items: new ToolProperty(
                name: 'type',
                type: PropertyType::from($input['items']['type']),
            )
        );
    }

    /**
     * @throws \ReflectionException
     */
    protected function createObjectProperty(string $name, PropertyType $type, bool $required, array $input): ObjectProperty
    {
        return new ObjectProperty(
            name: $name,
            description: $input['description'] ?? null,
            required: $required,
        );
    }
}
