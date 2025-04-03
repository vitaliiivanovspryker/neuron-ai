<?php

namespace NeuronAI\RAG\Embeddings;

use GuzzleHttp\Client;
use GuzzleHttp\RequestOptions;

class OllamaEmbeddingsProvider extends AbstractEmbeddingsProvider
{
    protected Client $client;

    public function __construct(
        protected string $model,
        protected string $url = 'http://localhost:11434/api',
    ) {
        $this->client = new Client(['base_uri' => trim($this->url, '/').'/']);
    }

    public function embedText(string $text): array
    {
        $response = $this->client->post('embed', [
            RequestOptions::JSON => [
                'model' => $this->model,
                'input' => $text,
            ]
        ])->getBody()->getContents();

        $response = json_decode($response, true);

        return $response['embeddings'][0];
    }
}
