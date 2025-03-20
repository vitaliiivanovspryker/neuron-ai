<?php

namespace NeuronAI\MCP;

use GuzzleHttp\Client;

class JsonRpcTransport implements MCPTransportInterface {
    private $client;
    private $serverUrl;

    public function __construct(string $serverUrl) {
        $this->serverUrl = $serverUrl;
        $this->client = new Client();
    }

    public function sendRequest(string $method, array $params, $id): array {
        $request = [
            'jsonrpc' => '2.0',
            'method' => $method,
            'params' => $params,
            'id' => $id
        ];

        try {
            $response = $this->client->request('POST', $this->serverUrl, [
                'headers' => [
                    'Content-Type' => 'application/json',
                ],
                'json' => $request
            ]);

            $result = json_decode($response->getBody()->getContents(), true);

            if (isset($result['error'])) {
                throw new \Exception("JSON-RPC Error: " . json_encode($result['error']));
            }

            return $result['result'];

        } catch (\GuzzleHttp\Exception\GuzzleException $e) {
            throw new \Exception("Request failed: " . $e->getMessage());
        }
    }
}
