<?php

namespace NeuronAI\MCP;

class StdioTransport implements MCPTransportInterface {
    private $inStream;
    private $outStream;

    public function __construct($inStream = STDIN, $outStream = STDOUT) {
        $this->inStream = $inStream;
        $this->outStream = $outStream;
    }

    public function sendRequest(string $method, array $params, $id): array {
        $request = [
            'jsonrpc' => '2.0',
            'method' => $method,
            'params' => $params,
            'id' => $id
        ];

        // Write the request to the output stream
        fwrite($this->outStream, json_encode($request) . PHP_EOL);
        fflush($this->outStream);

        // Read the response from the input stream
        $response = fgets($this->inStream);
        if ($response === false) {
            throw new \Exception("Failed to read response from input stream");
        }

        $result = json_decode($response, true);

        if ($result === null) {
            throw new \Exception("Invalid JSON response: " . $response);
        }

        if (isset($result['error'])) {
            throw new \Exception("JSON-RPC Error: " . json_encode($result['error']));
        }

        return $result['result'];
    }
}
