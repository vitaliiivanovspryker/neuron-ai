<?php

namespace NeuronAI\RAG\Embeddings;

use GuzzleHttp\Client;
use GuzzleHttp\RequestOptions;
use NeuronAI\RAG\Document;

class GeminiEmbeddingsProvider extends AbstractEmbeddingsProvider
{
    protected Client $client;

    protected string $baseUri = 'https://generativelanguage.googleapis.com/v1beta/models/';

    public function __construct(
        protected string $key,
        protected string $model,
        protected array $config = [
            'output_dimensionality' => 1024
        ]
    ) {
        $this->client = new Client([
            'headers' => [
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
                'x-goog-api-key' => $this->key,
            ]
        ]);
    }

    public function embedText(string $text): array
    {
        $response = $this->client->post($this->getUrl(), [
            RequestOptions::JSON => [
                'contents' => [
                    ['parts' => [['text' => $text]]]
                ],
                'embedding_config' => $this->config,
            ]
        ])->getBody()->getContents();

        $response = \json_decode($response, true);

        return $response['embeddings'][0]['values'];
    }

    public function embedDocuments(array $documents): array
    {
        $chunks = \array_chunk($documents, 100);

        foreach ($chunks as $chunk) {
            $response = $this->client->post($this->getUrl(), [
                RequestOptions::JSON => [
                    'contents' => \array_map(fn (Document $document): array => ['parts' => [['text' => $document->getContent()]]], $chunk),
                    'embedding_config' => $this->config,
                ]
            ])->getBody()->getContents();

            $response = \json_decode($response, true);

            foreach ($response['embeddings'][0]['values'] as $index => $item) {
                $chunk[$index]->embedding = $item['embedding'];
            }
        }

        return \array_merge(...$chunks);
    }

    protected function getUrl(): string
    {
        return \trim($this->baseUri, '/')."/{$this->model}:embedContent";
    }
}
