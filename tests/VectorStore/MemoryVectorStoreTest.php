<?php

namespace NeuronAI\Tests\VectorStore;

use NeuronAI\RAG\Document;
use NeuronAI\RAG\VectorStore\MemoryVectorStore;
use NeuronAI\RAG\VectorStore\VectorStoreInterface;
use PHPUnit\Framework\TestCase;

class MemoryVectorStoreTest extends TestCase
{
    protected array $embedding;

    protected function setUp(): void
    {
        // embedding "Hello World!"
        $this->embedding = json_decode(file_get_contents(__DIR__ . '/../stubs/hello-world.embeddings'), true);
    }

    public function test_memory_store_instance()
    {
        $store = new MemoryVectorStore();
        $this->assertInstanceOf(VectorStoreInterface::class, $store);
    }

    public function test_add_document_and_search()
    {
        $document = new Document('Hello World!');
        $document->embedding = $this->embedding;
        $document->hash = \hash('sha256', 'Hello World!' . time());

        $store = new MemoryVectorStore();
        $store->addDocument($document);

        $results = $store->similaritySearch($this->embedding);
        $this->assertIsArray($results);
    }

    public function test_similarity_search_with_scores()
    {
        $vectorStore = new MemoryVectorStore();

        $doc1 = new Document("Document 1");
        $doc1->embedding = [1, 0];
        $doc2 = new Document("Document 2");
        $doc2->embedding = [0, 1];
        $doc3 = new Document("Document 3");
        $doc3->embedding = [0.5, 0.5];

        $vectorStore->addDocuments([$doc1, $doc2, $doc3]);

        $results = $vectorStore->similaritySearch([1, 0]);

        $this->assertCount(3, $results);
        $this->assertGreaterThanOrEqual($results[1]->score, $results[0]->score);
        $this->assertGreaterThanOrEqual($results[2]->score, $results[1]->score);
    }
}
