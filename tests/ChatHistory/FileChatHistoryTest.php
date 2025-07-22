<?php

declare(strict_types=1);

namespace NeuronAI\Tests\ChatHistory;

use NeuronAI\Chat\History\FileChatHistory;
use NeuronAI\Chat\Messages\UserMessage;
use PHPUnit\Framework\TestCase;

class FileChatHistoryTest extends TestCase
{
    public function test_file_chat_history(): void
    {
        $history = new FileChatHistory(__DIR__, 'test');
        $this->assertFileDoesNotExist(__DIR__.\DIRECTORY_SEPARATOR.'neuron_test.chat');

        $history->addMessage(new UserMessage('Hello!'));
        $this->assertFileExists(__DIR__.\DIRECTORY_SEPARATOR.'neuron_test.chat');
        $this->assertCount(1, $history->getMessages());

        $history->flushAll();
        $this->assertFileDoesNotExist(__DIR__.\DIRECTORY_SEPARATOR.'neuron_test.chat');
        $this->assertCount(0, $history->getMessages());
    }

    public function test_file_chat_history_init(): void
    {
        $history = new FileChatHistory(__DIR__, 'test');
        $history->addMessage(new UserMessage('Hello!'));
        $this->assertFileExists(__DIR__.\DIRECTORY_SEPARATOR.'neuron_test.chat');

        $history = new FileChatHistory(__DIR__, 'test');
        $this->assertCount(1, $history->getMessages());
        $history->flushAll();
    }
}
