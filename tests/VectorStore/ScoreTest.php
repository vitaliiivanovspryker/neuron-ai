<?php

namespace Neuron\Tests\VectorStore;

use NeuronAI\RAG\Document;
use NeuronAI\RAG\VectorStore\MemoryVectorStore;
use PHPUnit\Framework\TestCase;

class ScoreTest extends TestCase
{
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
