<?php

declare(strict_types=1);

namespace NeuronAI\Tests\VectorStore;

use NeuronAI\RAG\Document;
use NeuronAI\RAG\VectorStore\QdrantVectorStore;
use NeuronAI\Tests\Traits\CheckOpenPort;
use PHPUnit\Framework\TestCase;

class QdrantVectorStoreTest extends TestCase
{
    use CheckOpenPort;

    protected QdrantVectorStore $store;

    public function setUp(): void
    {
        if (!$this->isPortOpen('127.0.0.1', 6333)) {
            $this->markTestSkipped('Port 6333 is not open. Skipping test.');
        }

        $this->store = new QdrantVectorStore('http://127.0.0.1:6333/collections/neuron-ai', '');
        $this->store->initialize(3, 'Cosine', true);
    }

    public function tearDown(): void
    {
        $this->store->destroy();
    }

    public function test_add_document_and_search(): void
    {
        $document = new Document('Hello World!');
        $document->addMetadata('customProperty', 'customValue');
        $document->embedding = [1, 2, 3];
        $document->id = 1;

        $this->store->addDocument($document);

        $results = $this->store->similaritySearch([1, 2, 3]);

        $this->assertEquals($document->getContent(), $results[0]->getContent());
        $this->assertEquals($document->metadata['customProperty'], $results[0]->metadata['customProperty']);
    }

    public function test_add_multiple_document_and_search(): void
    {
        $document = new Document('Hello!');
        $document->addMetadata('customProperty', 'customValue');
        $document->embedding = [1, 2, 3];
        $document->id = 1;

        $document2 = new Document('Hello 2!');
        $document2->embedding = [3, 4, 5];
        $document2->id = 2;

        $this->store->addDocuments([$document, $document2]);

        $results = $this->store->similaritySearch([1, 2, 3]);

        $this->assertEquals($document->getContent(), $results[0]->getContent());
        $this->assertEquals($document->metadata['customProperty'], $results[0]->metadata['customProperty']);
    }

    public function test_delete_documents(): void
    {
        $document = new Document('Hello!');
        $document->embedding = [1, 2, 3];
        $document->id = 1;

        $document2 = new Document('Hello 2!');
        $document2->embedding = [3, 4, 5];
        $document2->id = 2;

        $this->store->addDocuments([$document, $document2]);
        $this->store->deleteBySource('manual', 'manual');

        $results = $this->store->similaritySearch([1, 2, 3]);
        $this->assertCount(0, $results);
    }
}
