<?php

namespace NeuronAI\Tests;

use NeuronAI\RAG\DataLoader\StringDataLoader;
use PHPUnit\Framework\TestCase;

class EmbeddingsTest extends TestCase
{
    public function testSplitDocument()
    {
        $documents = StringDataLoader::for('test')->getDocuments();
        $this->assertCount(1, $documents);
        $this->assertEquals('test', $documents[0]->content);
        $this->assertEquals(\hash('sha256', 'test'), $documents[0]->hash);
    }
}
