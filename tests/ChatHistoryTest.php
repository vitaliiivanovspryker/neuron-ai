<?php

namespace NeuronAI\Tests;

use NeuronAI\AbstractChatHistory;
use NeuronAI\Chat\InMemoryChatHistory;
use NeuronAI\Chat\Messages\UserMessage;
use PHPUnit\Framework\TestCase;

class ChatHistoryTest extends TestCase
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

    public function testChatHistoryInstance()
    {
        $history = new InMemoryChatHistory();
        $this->assertInstanceOf(AbstractChatHistory::class, $history);
    }

    public function testChatHistoryAddMessage()
    {
        $history = new InMemoryChatHistory();
        $history->addMessage(new UserMessage('Hello!'));
        $this->assertCount(1, $history->getMessages());
        $this->assertEquals(1, $history->count());
    }

    public function testChatHistoryLastMessage()
    {
        $history = new InMemoryChatHistory();
        $history->addMessage(new UserMessage('Hello!'));
        $history->addMessage(new UserMessage('Hello2!'));
        $this->assertEquals('Hello2!', $history->getLastMessage()->getContent());
    }

    public function testChatHistoryTruncateOldMessages()
    {
        $history = new InMemoryChatHistory();
        $history->addMessage(new UserMessage('Hello!'));
        $history->addMessage(new UserMessage('Hello2!'));
        $history->truncate(1);
        $this->assertEquals(1, $history->count());
        $this->assertEquals('Hello2!', $history->getLastMessage()->getContent());
    }

    public function testChatHistoryClear()
    {
        $history = new InMemoryChatHistory();
        $history->addMessage(new UserMessage('Hello!'));
        $history->addMessage(new UserMessage('Hello2!'));
        $history->clear();
        $this->assertCount(0, $history->getMessages());
        $this->assertEquals(0, $history->count());
    }
}
