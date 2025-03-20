<?php

namespace NeuronAI\MCP;

/*
// Using JSON-RPC
$jsonRpcTransport = new JsonRpcTransport('https://your-mcp-server.com/endpoint');
$client = new MCPClient($jsonRpcTransport);
$tools = $client->getTools();
$resources = $client->getResources();

// Using stdio transport
$stdioTransport = new StdioTransport();
$client = new MCPClient($stdioTransport);
$tools = $client->getTools();
$resources = $client->getResources();
 */

use NeuronAI\Tools\Tool;

class MCPClient {
    private $requestId = 1;

    public function __construct(protected MCPTransportInterface $transport) {}

    /**
     * Get the list of available tools from the MCP server
     *
     * @return array<Tool>
     * @throws \Exception
     */
    public function getTools(): array
    {
        // todo: create Tools with a callTool function to the MCP server as callable
        return $this->transport->sendRequest('mcp.listTools', [], $this->requestId++);
    }

    /**
     * Call a specific tool on the MCP server
     *
     * @param string $toolName The name of the tool to call
     * @param array $toolParameters The parameters to pass to the tool
     * @return array The result of the tool execution
     * @throws \Exception
     */
    public function callTool(string $toolName, array $toolParameters): array
    {
        $params = [
            'tool' => $toolName,
            'parameters' => $toolParameters
        ];

        return $this->transport->sendRequest('mcp.runTool', $params, $this->requestId++);
    }

    /**
     * Get the list of available resources from the MCP server
     */
    public function getResources()
    {
        return $this->transport->sendRequest('mcp.listResources', [], $this->requestId++);
    }

    /**
     * Get a specific resource from the MCP server
     *
     * @param string $resourceId The ID of the resource to retrieve
     * @return array The resource data
     * @throws \Exception
     */
    public function getResource(string $resourceId): array
    {
        $params = ['id' => $resourceId];

        return $this->transport->sendRequest('mcp.getResource', $params, $this->requestId++);
    }
}
