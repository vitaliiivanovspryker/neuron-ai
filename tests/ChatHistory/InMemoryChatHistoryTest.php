<?php

declare(strict_types=1);

namespace NeuronAI\Tests\ChatHistory;

use NeuronAI\Chat\History\AbstractChatHistory;
use NeuronAI\Chat\History\InMemoryChatHistory;
use NeuronAI\Chat\Messages\AssistantMessage;
use NeuronAI\Chat\Messages\Message;
use NeuronAI\Chat\Messages\ToolCallMessage;
use NeuronAI\Chat\Messages\ToolCallResultMessage;
use NeuronAI\Chat\Messages\Usage;
use NeuronAI\Chat\Messages\UserMessage;
use NeuronAI\Tools\Tool;
use PHPUnit\Framework\TestCase;

class InMemoryChatHistoryTest extends TestCase
{
    private InMemoryChatHistory $chatHistory;

    protected function setUp(): void
    {
        parent::setUp();
        // Use a small context window for testing
        $this->chatHistory = new InMemoryChatHistory(1000);
    }

    public function test_chat_history_instance(): void
    {
        $history = new InMemoryChatHistory();
        $this->assertInstanceOf(AbstractChatHistory::class, $history);
    }

    public function test_chat_history_add_message(): void
    {
        $history = new InMemoryChatHistory();
        $history->addMessage(new UserMessage('Hello!'));
        $this->assertCount(1, $history->getMessages());
    }

    public function test_chat_history_truncate(): void
    {
        $history = new InMemoryChatHistory(6);

        $message = new UserMessage('Hello!');
        $history->addMessage($message);
        $this->assertEquals(6, $history->calculateTotalUsage());

        $message = new UserMessage('Hello!');
        $history->addMessage($message);
        $this->assertEquals(6, $history->calculateTotalUsage());
        $this->assertCount(1, $history->getMessages());
    }

    public function test_chat_history_clear(): void
    {
        $history = new InMemoryChatHistory();
        $history->addMessage(new UserMessage('Hello!'));
        $history->addMessage(new UserMessage('Hello2!'));
        $history->flushAll();
        $this->assertCount(0, $history->getMessages());
    }

    public function test_tokens_calculation(): void
    {
        $history = new InMemoryChatHistory(300);

        $message = new UserMessage('Hello!');
        $message->setUsage(new Usage(100, 100));
        $history->addMessage($message);
        $this->assertEquals(200, $history->calculateTotalUsage());
        $this->assertEquals(100, $history->getFreeMemory());

        $message = new UserMessage('Hello!');
        $message->setUsage(new Usage(100, 100));
        $history->addMessage($message);
        $this->assertEquals(100, $history->getFreeMemory());
        $this->assertEquals(200, $history->calculateTotalUsage());
    }

    public function testToolCallPairIsRemovedTogether(): void
    {
        // Create a tool for testing
        $tool = Tool::make('test_tool', 'A test tool')
            ->setInputs(['param' => 'value'])
            ->setCallId('call_123');

        $toolWithResult = Tool::make('test_tool', 'A test tool')
            ->setInputs(['param' => 'value'])
            ->setCallId('call_123')
            ->setResult('Tool execution result');

        // Add messages that will fill up the context window
        $largeMessage1 = new UserMessage('Large message 1');
        $largeMessage1->setUsage(new Usage(400, 0)); // Large input tokens
        $this->chatHistory->addMessage($largeMessage1);

        $toolCallMessage = new ToolCallMessage('Calling test tool', [$tool]);
        $toolCallMessage->setUsage(new Usage(200, 50));
        $this->chatHistory->addMessage($toolCallMessage);

        $toolResultMessage = new ToolCallResultMessage([$toolWithResult]);
        $toolResultMessage->setUsage(new Usage(100, 0));
        $this->chatHistory->addMessage($toolResultMessage);

        // Add another large message that should trigger context window cutting
        $largeMessage2 = new UserMessage('Large message 2');
        $largeMessage2->setUsage(new Usage(400, 0)); // This should trigger cutting
        $this->chatHistory->addMessage($largeMessage2);

        $messages = $this->chatHistory->getMessages();

        // Verify that both tool call and tool result messages were removed together
        $hasToolCall = false;
        $hasToolResult = false;

        foreach ($messages as $message) {
            if ($message instanceof ToolCallMessage) {
                $hasToolCall = true;
            }
            if ($message instanceof ToolCallResultMessage) {
                $hasToolResult = true;
            }
        }

        // Both should be absent (removed together) or both should be present
        $this->assertEquals($hasToolCall, $hasToolResult, 'Tool call and tool result messages should be removed together');
    }

    public function testMultipleToolCallPairsAreHandledCorrectly(): void
    {
        // Create two different tools
        $tool1 = Tool::make('tool_1', 'First tool')
            ->setInputs(['param1' => 'value1'])
            ->setCallId('call_1');

        $tool1WithResult = Tool::make('tool_1', 'First tool')
            ->setInputs(['param1' => 'value1'])
            ->setCallId('call_1')
            ->setResult('First tool result');

        $tool2 = Tool::make('tool_2', 'Second tool')
            ->setInputs(['param2' => 'value2'])
            ->setCallId('call_2');

        $tool2WithResult = Tool::make('tool_2', 'Second tool')
            ->setInputs(['param2' => 'value2'])
            ->setCallId('call_2')
            ->setResult('Second tool result');

        // Add the first tool call pair
        $toolCall1 = new ToolCallMessage('Calling first tool', [$tool1]);
        $this->chatHistory->addMessage($toolCall1);

        $toolResult1 = new ToolCallResultMessage([$tool1WithResult]);
        $this->chatHistory->addMessage($toolResult1);

        // Add the second tool call pair
        $toolCall2 = new ToolCallMessage('Calling second tool', [$tool2]);
        $this->chatHistory->addMessage($toolCall2);

        $toolResult2 = new ToolCallResultMessage([$tool2WithResult]);
        $this->chatHistory->addMessage($toolResult2);

        // Add a large message that should trigger context window cutting
        $largeMessage = new UserMessage('Large message');
        $this->chatHistory->addMessage($largeMessage);

        $messages = $this->chatHistory->getMessages();

        // Check that we have consistent tool call/result pairs
        $toolCallNames = [];
        $toolResultNames = [];

        foreach ($messages as $message) {
            if ($message instanceof ToolCallMessage) {
                foreach ($message->getTools() as $tool) {
                    $toolCallNames[] = $tool->getName();
                }
            }
            if ($message instanceof ToolCallResultMessage) {
                foreach ($message->getTools() as $tool) {
                    $toolResultNames[] = $tool->getName();
                }
            }
        }

        \sort($toolCallNames);
        \sort($toolResultNames);

        $this->assertEquals($toolCallNames, $toolResultNames, 'Tool call names should match tool result names');
    }

    public function testRegularMessagesAreRemovedWhenContextWindowExceeded(): void
    {
        // Add several regular messages that exceed context window
        for ($i = 0; $i < 5; $i++) {
            $message = new UserMessage("Message $i");
            $message->setUsage(new Usage(250, 0)); // Each message uses 250 tokens
            $this->chatHistory->addMessage($message);
        }

        $remainingMessages = $this->chatHistory->getMessages();

        // With the context window of 1000, we should have fewer than 5 messages
        $this->assertLessThan(5, \count($remainingMessages), 'Some messages should be removed due to context window limit');

        // Verify total usage is within the context window
        $this->assertLessThanOrEqual(1000, $this->chatHistory->calculateTotalUsage());
    }

    public function testContextWindowRespectedWithMixedMessageTypes(): void
    {
        $tool = Tool::make('mixed_tool', 'A mixed tool')
            ->setInputs(['param' => 'value'])
            ->setCallId('mixed_call');

        $toolWithResult = Tool::make('mixed_tool', 'A mixed tool')
            ->setInputs(['param' => 'value'])
            ->setCallId('mixed_call')
            ->setResult('Mixed tool result');

        // Add a mix of different message types
        $userMessage = new UserMessage('User message');
        $userMessage->setUsage(new Usage(200, 0));
        $this->chatHistory->addMessage($userMessage);

        $assistantMessage = new AssistantMessage('Assistant response');
        $assistantMessage->setUsage(new Usage(150, 100));
        $this->chatHistory->addMessage($assistantMessage);

        $toolCall = new ToolCallMessage('Tool call', [$tool]);
        $toolCall->setUsage(new Usage(200, 50));
        $this->chatHistory->addMessage($toolCall);

        $toolResult = new ToolCallResultMessage([$toolWithResult]);
        $toolResult->setUsage(new Usage(100, 0));
        $this->chatHistory->addMessage($toolResult);

        // Add a large message that should trigger cutting
        $largeMessage = new UserMessage('Very large message');
        $largeMessage->setUsage(new Usage(400, 0));
        $this->chatHistory->addMessage($largeMessage);

        // Verify context window is respected
        $this->assertLessThanOrEqual(1000, $this->chatHistory->calculateTotalUsage());

        $messages = $this->chatHistory->getMessages();

        // Verify we still have some messages
        $this->assertGreaterThan(0, \count($messages));
    }

    public function testFindCorrespondingToolResultMethod(): void
    {
        $tool1 = Tool::make('tool_1', 'First tool')
            ->setInputs(['param' => 'value'])
            ->setCallId('call_1');

        $tool1WithResult = Tool::make('tool_1', 'First tool')
            ->setInputs(['param' => 'value'])
            ->setCallId('call_1')
            ->setResult('Result 1');

        $tool2 = Tool::make('tool_2', 'Second tool')
            ->setInputs(['param' => 'value'])
            ->setCallId('call_2');

        $tool2WithResult = Tool::make('tool_2', 'Second tool')
            ->setInputs(['param' => 'value'])
            ->setCallId('call_2')
            ->setResult('Result 2');

        // Add messages in specific order
        $regularMessage = new UserMessage('Regular message');
        $regularMessage->setUsage(new Usage(100, 0));
        $this->chatHistory->addMessage($regularMessage);

        $toolCall1 = new ToolCallMessage('First tool call', [$tool1]);
        $toolCall1->setUsage(new Usage(100, 0));
        $this->chatHistory->addMessage($toolCall1);

        $toolResult1 = new ToolCallResultMessage([$tool1WithResult]);
        $toolResult1->setUsage(new Usage(100, 0));
        $this->chatHistory->addMessage($toolResult1);

        $toolCall2 = new ToolCallMessage('Second tool call', [$tool2]);
        $toolCall2->setUsage(new Usage(100, 0));
        $this->chatHistory->addMessage($toolCall2);

        $toolResult2 = new ToolCallResultMessage([$tool2WithResult]);
        $toolResult2->setUsage(new Usage(100, 0));
        $this->chatHistory->addMessage($toolResult2);

        // Use reflection to test the protected method
        $reflection = new \ReflectionClass($this->chatHistory);
        $method = $reflection->getMethod('findCorrespondingToolResult');

        // Test finding the corresponding result for the first tool call (index 1)
        $correspondingIndex = $method->invoke($this->chatHistory, 1);
        $this->assertEquals(2, $correspondingIndex, 'Should find corresponding tool result at index 2');

        // Test finding the corresponding result for the second tool call (index 3)
        $correspondingIndex = $method->invoke($this->chatHistory, 3);
        $this->assertEquals(4, $correspondingIndex, 'Should find corresponding tool result at index 4');
    }

    public function testGetFreeMemoryCalculation(): void
    {
        // Add messages with known token usage
        $message1 = new UserMessage('Test message 1');
        $message1->setUsage(new Usage(100, 50)); // Total: 150
        $this->chatHistory->addMessage($message1);

        $message2 = new AssistantMessage('Test message 2');
        $message2->setUsage(new Usage(200, 100)); // Total: 300
        $this->chatHistory->addMessage($message2);

        // Total usage should be 150 + 300 = 450
        // Free memory should be 1000 - 450 = 550
        $this->assertEquals(550, $this->chatHistory->getFreeMemory());
    }

    public function testEmptyHistoryAfterFlushAll(): void
    {
        // Add some messages
        $this->chatHistory->addMessage(new UserMessage('Test message'));
        $this->chatHistory->addMessage(new AssistantMessage('Test response'));

        $this->assertGreaterThan(0, \count($this->chatHistory->getMessages()));

        // Flush all messages
        $this->chatHistory->flushAll();

        $this->assertEmpty($this->chatHistory->getMessages());
        $this->assertEquals(0, $this->chatHistory->calculateTotalUsage());
        $this->assertEquals(1000, $this->chatHistory->getFreeMemory());
    }

    public function testHistoryKeysAreRecalculatedAfterCutting(): void
    {
        // Add messages that will exceed context window
        for ($i = 0; $i < 6; $i++) {
            $message = new UserMessage("Message $i");
            $message->setUsage(new Usage(200, 0));
            $this->chatHistory->addMessage($message);
        }

        $messages = $this->chatHistory->getMessages();

        // Check that array keys are sequential (0, 1, 2, ...)
        $expectedKeys = \array_keys($messages);
        $actualKeys = \range(0, \count($messages) - 1);

        $this->assertEquals($actualKeys, $expectedKeys, 'Array keys should be sequential after cutting');
    }
}
