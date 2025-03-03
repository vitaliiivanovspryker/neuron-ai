<?php

namespace NeuronAI\RAG\VectorStore;

use GuzzleHttp\Client;
use GuzzleHttp\RequestOptions;
use NeuronAI\RAG\Document;

class PineconeVectorStore implements VectorStoreInterface
{
    protected Client $client;

    public function __construct(
        string $key,
        protected string $index,
        array $spec,
        string $version = '2025-01'
    ) {
        $this->client = new Client([
            'base_uri' => 'https://api.pinecone.io',
            'headers' => [
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
                'Api-Key' => $key,
                'X-Pinecone-API-Version' => $version,
            ]
        ]);

        $response = $this->client->get("indexes/{$this->index}");

        if ($response->getStatusCode() === 200) {
            return;
        }

        // Create the index
        $this->client->post('indexes', [
            RequestOptions::JSON => [
                'name' => $index,
                'spec' => $spec,
            ]
        ]);
    }

    public function addDocument(Document $document): void
    {
        $this->addDocuments([$document]);
    }

    public function addDocuments(array $documents): void
    {
        $this->client->post("indexes/{$this->index}/vectors/upsert", [
            RequestOptions::JSON => array_map(function (Document $document) {
                return [
                    'id' => $document->id??uniqid(),
                    'values' => $document->embedding,
                ];
            }, $documents)
        ]);
    }

    public function similaritySearch(array $embedding, int $k = 4): iterable
    {
        $result = $this->client->get("indexes/{$this->index}/query", [
            RequestOptions::QUERY => [
                'namespace' => '',
                'vector' => $embedding,
                'top_k' => $k,
            ]
        ])->getBody()->getContents();

        $result = json_decode($result, true);

        return $result['matches'];
    }
}
