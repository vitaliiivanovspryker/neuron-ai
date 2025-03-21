<?php

namespace NeuronAI\MCP;

class McpClient
{
    private McpTransportInterface $transport;

    private int $requestId = 0;

    /**
     * Create a new MCP client with the given transport
     */
    public function __construct(array $config)
    {
        if (\array_key_exists('command', $config)) {
            $this->transport = new StdioTransport($config);
            $this->transport->connect();
        }

        throw new \Exception('Transport not supported!');
    }

    /*public function connect(): void
    {
        $this->transport->connect();
    }

    public function disconnect(): void
    {
        $this->transport->disconnect();
    }*/

    /**
     * List all available tools from the MCP server
     *
     * @throws \Exception
     */
    public function listTools($cursor = null): array
    {
        $request = [
            "jsonrpc" => "2.0",
            "id" => ++$this->requestId,
            "method" => "tools/list",
            "params" => [
                "cursor" => $cursor
            ]
        ];

        $this->transport->send($request);
        return $this->transport->receive();
    }

    /**
     * Call a tool on the MCP server
     *
     * @throws \Exception
     */
    public function callTool($toolName, $parameters = []): array
    {
        $request = [
            "jsonrpc" => "2.0",
            "id" => ++$this->requestId,
            "method" => "tools/call",
            "params" => [
                "name" => $toolName,
                "parameters" => $parameters
            ]
        ];

        $this->transport->send($request);
        return $this->transport->receive();
    }
}
