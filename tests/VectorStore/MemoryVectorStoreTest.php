<?php

namespace NeuronAI\Tests\VectorStore;

use NeuronAI\RAG\Document;
use NeuronAI\RAG\VectorStore\MemoryVectorStore;
use NeuronAI\RAG\VectorStore\VectorStoreInterface;
use PHPUnit\Framework\TestCase;

class MemoryVectorStoreTest extends TestCase
{
    protected function setUp(): void
    {
    }

    public function testVectorStoreInstance()
    {
        $store = new MemoryVectorStore();
        $this->assertInstanceOf(VectorStoreInterface::class, $store);
    }

    public function testAddDocument()
    {
        $this->expectNotToPerformAssertions();

        $store = new MemoryVectorStore();
        $store->addDocument(new Document('Hello'));
    }
}
