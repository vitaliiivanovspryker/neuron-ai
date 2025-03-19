<?php

namespace NeuronAI\RAG\Embeddings;

use GuzzleHttp\Client;
use NeuronAI\RAG\Document;

class OpenAIEmbeddingsProvider extends AbstractEmbeddingProvider
{
    protected Client $client;

    protected string $baseUri = 'https://api.openai.com/v1/embeddings';

    public function __construct(
        protected string $key,
        protected string $model
    ) {
        $this->client = new Client([
            'base_uri' => $this->baseUri,
            'headers' => [
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
                'Authorization' => 'Bearer ' . $this->key,
            ]
        ]);
    }

    public function embedText(string $text): array
    {
        $response = $this->client->post('', [
            'json' => [
                'model' => $this->model,
                'input' => $text,
                'encoding_format' => 'float',
            ]
        ]);

        $response = \json_decode($response->getBody()->getContents(), true);

        return $response['data'][0]['embedding'];
    }

    public function embedDocument(Document $document): Document
    {
        $text = $document->formattedContent ?? $document->content;
        $document->embedding = $this->embedText($text);

        return $document;
    }
}
