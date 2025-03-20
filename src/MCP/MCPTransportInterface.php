<?php

namespace NeuronAI\MCP;

interface MCPTransportInterface {
    /**
     * Send a request to the MCP server using the specific transport
     *
     * @param string $method The method to call
     * @param array $params The parameters for the method
     * @param mixed $id The request ID
     * @return array The result from the server
     * @throws \Exception If the request fails
     */
    public function sendRequest(string $method, array $params, $id): array;
}
