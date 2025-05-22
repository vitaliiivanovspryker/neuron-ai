<?php

namespace Tests\PostProcessor;

use NeuronAI\Chat\Messages\UserMessage;
use NeuronAI\RAG\Document;
use NeuronAI\RAG\PostProcessor\FixedThresholdPostProcessor;
use PHPUnit\Framework\TestCase;

class FixedThresholdPostProcessorTest extends TestCase
{
    public function test_process_filters_documents_below_threshold(): void
    {
        $doc1 = new Document();
        $doc1->score = 0.8;

        $doc2 = new Document();
        $doc2->score = 0.3;

        $doc3 = new Document();
        $doc3->score = 0.7;

        $doc4 = new Document();
        $doc4->score = 0.4;

        $doc5 = new Document();
        $doc5->score = 0.9;

        $documents = [$doc1, $doc2, $doc3, $doc4, $doc5];

        $processor = new FixedThresholdPostProcessor(0.8);
        $question = new UserMessage('test question');

        $result = $processor->process($question, $documents);

        $this->assertCount(2, $result);
        $this->assertEquals(0.8, $result[0]->score);
        $this->assertEquals(0.9, $result[1]->score);
    }
}
