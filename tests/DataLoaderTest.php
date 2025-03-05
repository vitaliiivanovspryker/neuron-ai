<?php

namespace NeuronAI\Tests;

use NeuronAI\RAG\DataLoader\StringDataLoader;
use PHPUnit\Framework\TestCase;

class DataLoaderTest extends TestCase
{
    public function testMessage()
    {
        $result = StringDataLoader::for('Hello')->getDocuments();
        $this->assertIsArray($result);
    }
}
