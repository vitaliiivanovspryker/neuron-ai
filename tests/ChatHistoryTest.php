<?php

namespace NeuronAI\Tests;

use NeuronAI\Chat\History\AbstractChatHistory;
use NeuronAI\Chat\History\FileChatHistory;
use NeuronAI\Chat\History\InMemoryChatHistory;
use NeuronAI\Chat\Messages\Message;
use NeuronAI\Chat\Messages\Usage;
use NeuronAI\Chat\Messages\UserMessage;
use NeuronAI\Exceptions\ChatHistoryException;
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
    }

    public function testChatHistoryTruncate()
    {
        $message = new UserMessage('Hello!');
        $message->setUsage(new Usage(100, 100));
        $history = new InMemoryChatHistory(300);
        $history->addMessage($message);
        $history->addMessage($message);
        $this->assertCount(1, $history->getMessages());
    }

    public function testChatHistoryClear()
    {
        $history = new InMemoryChatHistory();
        $history->addMessage(new UserMessage('Hello!'));
        $history->addMessage(new UserMessage('Hello2!'));
        $history->clear();
        $this->assertCount(0, $history->getMessages());
    }

    /**
     * @throws ChatHistoryException
     */
    public function testFileChatHistory()
    {
        $history = new FileChatHistory(__DIR__, 'test');
        $this->assertFileDoesNotExist(__DIR__.DIRECTORY_SEPARATOR.'neuron_test.chat');

        $history->addMessage(new UserMessage('Hello!'));
        $this->assertFileExists(__DIR__.DIRECTORY_SEPARATOR.'neuron_test.chat');
        $this->assertCount(1, $history->getMessages());

        $history->clear();
        $this->assertFileDoesNotExist(__DIR__.DIRECTORY_SEPARATOR.'neuron_test.chat');
        $this->assertCount(0, $history->getMessages());
    }

    public function testFileChatHistoryInit()
    {
        $history = new FileChatHistory(__DIR__, 'test');
        $history->addMessage(new UserMessage('Hello!'));
        $this->assertFileExists(__DIR__.DIRECTORY_SEPARATOR.'neuron_test.chat');

        $history = new FileChatHistory(__DIR__, 'test');
        $this->assertCount(1, $history->getMessages());
        $history->clear();
    }
}
