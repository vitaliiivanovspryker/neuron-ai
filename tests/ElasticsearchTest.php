<?php

namespace NeuronAI\Tests;

use Elastic\Elasticsearch\Client;
use Elastic\Elasticsearch\ClientBuilder;
use NeuronAI\RAG\Document;
use NeuronAI\RAG\VectorStore\ElasticsearchVectorStore;
use NeuronAI\RAG\VectorStore\VectorStoreInterface;
use PHPUnit\Framework\TestCase;

class ElasticsearchTest extends TestCase
{
    protected Client $client;

    protected array $embedding;

    protected function setUp(): void
    {
        if (!$this->isPortOpen('127.0.0.1', 9200)) {
            $this->markTestSkipped('Port 9300 is not open. Skipping test.');
        }

        $this->client = ClientBuilder::create()
            ->setHosts(['http://127.0.0.1:9300'])
            ->build();

        // embedding "Hello World!"
        $this->embedding = json_decode(file_get_contents(__DIR__ . '/stubs/hello-world.embeddings'), true);
    }

    private function isPortOpen(string $host, int $port, int $timeout = 1): bool
    {
        $connection = @fsockopen($host, $port, $errno, $errstr, $timeout);
        if (is_resource($connection)) {
            fclose($connection);
            return true;
        }
        return false;
    }

    public function testElasticsearchInstance()
    {
        $store = new ElasticsearchVectorStore($this->client, 'test');
        $this->assertInstanceOf(VectorStoreInterface::class, $store);
    }

    public function testAddDocument()
    {
        $this->expectNotToPerformAssertions();

        $document = new Document('Hello World!');
        $document->embedding = $this->embedding;
        $document->hash = \hash('sha256', 'Hello World!' . time());

        $store = new ElasticsearchVectorStore($this->client, 'test');

        $store->addDocument($document);
    }

    public function testSearch()
    {
        $store = new ElasticsearchVectorStore($this->client, 'test');

        $results = $store->similaritySearch($this->embedding);
        $this->assertIsArray($results);
    }
}
