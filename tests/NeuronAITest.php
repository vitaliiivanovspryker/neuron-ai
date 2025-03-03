<?php

namespace NeuronAI\Tests;


use NeuronAI\AbstractChatHistory;
use NeuronAI\Agent;
use NeuronAI\InMemoryChatHistory;
use NeuronAI\Messages\UserMessage;
use NeuronAI\RAG\VectorStore\MemoryVectorStore;
use NeuronAI\RAG\VectorStore\VectorStoreInterface;
use PHPUnit\Framework\TestCase;

class NeuronAITest extends TestCase
{
    /**
     * Sets up the fixture, for example, open a network connection.
     * This method is called before a test is executed.
     *
     * @throws \Exception
     */
    public function setUp(): void
    {
    }

    public function testInstance()
    {
        $neuron = new Agent();
        $this->assertInstanceOf(Agent::class, $neuron);
        $this->assertInstanceOf(\SplSubject::class, $neuron);
    }

    public function testChatHistory()
    {
        $history = new InMemoryChatHistory();
        $this->assertInstanceOf(AbstractChatHistory::class, $history);

        $history->addMessage(new UserMessage('Hello!'));
        $this->assertCount(1, $history->getMessages());
        $this->assertEquals(1, $history->count());

        $history->addMessage(new UserMessage('Hello2!'));
        $this->assertEquals('Hello2!', $history->getLastMessage()->getContent());

        $history->truncate(1);
        $this->assertEquals(1, $history->count());
        $this->assertEquals('Hello2!', $history->getLastMessage()->getContent());

        $history->clear();
        $this->assertCount(0, $history->getMessages());
        $this->assertEquals(0, $history->count());
    }

    public function testVectorStore()
    {
        $store = new MemoryVectorStore();
        $this->assertInstanceOf(VectorStoreInterface::class, $store);
    }
}
