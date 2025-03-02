<?php

namespace App\Extensions\NeuronAI\RAG\VectorStore;

use App\Extensions\NeuronAI\RAG\Document;
use \Probots\Pinecone\Client;

class PineconeVectorStore implements VectorStoreInterface
{
    public function __construct(
        protected Client $client,
        protected string $indexName
    ) {
        // todo: setup the vector index

        // https://github.com/probots-io/pinecone-php
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
