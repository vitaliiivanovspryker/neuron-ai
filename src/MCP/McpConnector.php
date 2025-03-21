<?php

namespace NeuronAI\MCP;

use NeuronAI\StaticConstructor;
use NeuronAI\Tools\ToolInterface;

class McpConnector
{
    use StaticConstructor;

    protected McpClient $client;

    public function __construct(array $config)
    {
        $this->client = new McpClient($config);
    }

    public function tools()
    {
        $tools = $this->client->listTools();

        return \array_map(function ($tool) {
            return $this->createTool($tool);
        }, $tools);
    }

    protected function createTool(array $item): ToolInterface
    {
        $tool = \NeuronAI\Tools\Tool::make(
            name: $item['name'],
            description: $item['description']
        )->setCallable(function (...$args) use ($item) {
            $response = call_user_func([$this->client, 'callTool'], $item['name'], $args);
            $response = $response['result']['content'][0];

            if ($response['type'] === 'text') {
                return $response['text'];
            }

            if ($response['type'] === 'image') {
                return $response;
            }

            throw new \Exception("Tool response format not supported: {$response['type']}");
        });

        foreach ($item['inputSchema']['properties'] as $name => $input) {
            $tool->addProperty(
                new \NeuronAI\Tools\ToolProperty(
                    $name,
                    $input['type'],
                    $input['description'],
                    \in_array($name, $item['inputSchema']['required'])
                )
            );
        }

        return $tool;
    }
}
