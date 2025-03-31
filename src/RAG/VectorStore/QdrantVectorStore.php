<?php

namespace NeuronAI\RAG\VectorStore;

use GuzzleHttp\Client;
use NeuronAI\RAG\Document;

class QdrantVectorStore implements VectorStoreInterface
{
    protected Client $client;

    public function __construct(
        protected string $key,
        protected int $vectorDimension,
        protected string $collectionUrl // like http://localhost:6333/collections/neuron-ai
    )
    {
        $this->client = new Client([
            'base_uri' => $this->collectionUrl,
            'headers' => [
                'Content-Type' => 'application/json',
                'api-key' => $this->key,
            ]
        ]);
    }

    public function addDocument(Document $document): void
    {
        // TODO: Implement addDocument() method.
    }

    public function addDocuments(array $documents): void
    {
        // TODO: Implement addDocuments() method.
    }

    public function similaritySearch(array $embedding, int $k = 4): iterable
    {
        // TODO: Implement similaritySearch() method.
    }
}
