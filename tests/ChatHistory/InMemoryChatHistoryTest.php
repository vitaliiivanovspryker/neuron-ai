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
        $history = new InMemoryChatHistory(300);
        $this->assertEquals(300, $history->getFreeMemory());

        $message = new UserMessage('Hello!');
        $message->setUsage(new Usage(100, 100));
        $history->addMessage($message);
        $this->assertEquals(100, $history->getFreeMemory());
        $this->assertEquals(200, $history->calculateTotalUsage());

        $message = new UserMessage('Hello!');
        $message->setUsage(new Usage(300, 100));
        $history->addMessage($message);
        $this->assertEquals(0, $history->getFreeMemory());
        $this->assertEquals(300, $history->calculateTotalUsage());
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
        $message = new UserMessage('Hello!');
        $message->setUsage(new Usage(100, 100));

        $history = new InMemoryChatHistory(300);
        $history->addMessage($message);
        $this->assertEquals(200, $history->calculateTotalUsage());
        $this->assertEquals(100, $history->getFreeMemory());

        $message = new UserMessage('Hello!');
        $message->setUsage(new Usage(200, 100));
        $history->addMessage($message);
        $this->assertEquals(100, $history->getFreeMemory());
        $this->assertEquals(200, $history->calculateTotalUsage());
    }

    /**
     * Helper method to create a message with usage that simulates cumulative input tokens
     * from the API (total input tokens including all previous messages)
     */
    private function createMessageWithCumulativeUsage(
        string $content,
        int    $cumulativeInputTokens,
        int    $outputTokens,
        string $type = 'user'
    ): Message {
        $message = match ($type) {
            'assistant' => new AssistantMessage($content),
            'user' => new UserMessage($content),
            default => new UserMessage($content)
        };

        $message->setUsage(new Usage($cumulativeInputTokens, $outputTokens));

        return $message;
    }

    private function createToolCallWithCumulativeUsage(
        string $content,
        array  $tools,
        int    $cumulativeInputTokens,
        int    $outputTokens
    ): ToolCallMessage {
        $message = new ToolCallMessage($content, $tools);
        $message->setUsage(new Usage($cumulativeInputTokens, $outputTokens));

        return $message;
    }

    private function createToolResultWithCumulativeUsage(
        array $tools,
        int   $cumulativeInputTokens,
        int   $outputTokens = 0
    ): ToolCallResultMessage {
        $message = new ToolCallResultMessage($tools);
        $message->setUsage(new Usage($cumulativeInputTokens, $outputTokens));

        return $message;
    }

    public function testToolCallPairIsRemovedTogether(): void
    {
        $tool = Tool::make('test_tool', 'A test tool')
            ->setInputs(['param' => 'value'])
            ->setCallId('call_123');

        $toolWithResult = Tool::make('test_tool', 'A test tool')
            ->setInputs(['param' => 'value'])
            ->setCallId('call_123')
            ->setResult('Tool execution result');

        // First message: 300 input tokens total
        $largeMessage1 = $this->createMessageWithCumulativeUsage('Large message 1', 300, 0);
        $this->chatHistory->addMessage($largeMessage1);

        // After adding first message, it should have 300 marginal input tokens
        $this->assertEquals(300, $largeMessage1->getUsage()->inputTokens);

        // Second message: tool call with 500 total input tokens (200 marginal)
        $toolCallMessage = $this->createToolCallWithCumulativeUsage('Calling test tool', [$tool], 500, 50);
        $this->chatHistory->addMessage($toolCallMessage);

        // Should have 200 marginal input tokens (500 - 300)
        $this->assertEquals(200, $toolCallMessage->getUsage()->inputTokens);

        // Third message: tool result with 600 total input tokens (100 marginal)
        $toolResultMessage = $this->createToolResultWithCumulativeUsage([$toolWithResult], 600, 0);
        $this->chatHistory->addMessage($toolResultMessage);

        // Should have 100 marginal input tokens (600 - 500)
        $this->assertEquals(100, $toolResultMessage->getUsage()->inputTokens);

        // Fourth message: large message with 1200 total input tokens (600 marginal)
        // This should trigger context window cutting since total will exceed 1000
        $largeMessage2 = $this->createMessageWithCumulativeUsage('Large message 2', 1200, 0);
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

        // Verify total usage is within context window
        $this->assertLessThanOrEqual(1000, $this->chatHistory->calculateTotalUsage());
    }

    public function testMultipleToolCallPairsAreHandledCorrectly(): void
    {
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

        // First tool call: 250 total input tokens
        $cumulativeInput = 250;
        $toolCall1 = $this->createToolCallWithCumulativeUsage('Calling first tool', [$tool1], $cumulativeInput, 50);
        $this->chatHistory->addMessage($toolCall1);

        // First tool result: 350 total input tokens
        $cumulativeInput = 350;
        $toolResult1 = $this->createToolResultWithCumulativeUsage([$tool1WithResult], $cumulativeInput, 0);
        $this->chatHistory->addMessage($toolResult1);

        // Second tool call: 550 total input tokens
        $cumulativeInput = 550;
        $toolCall2 = $this->createToolCallWithCumulativeUsage('Calling second tool', [$tool2], $cumulativeInput, 50);
        $this->chatHistory->addMessage($toolCall2);

        // Second tool result: 650 total input tokens
        $cumulativeInput = 650;
        $toolResult2 = $this->createToolResultWithCumulativeUsage([$tool2WithResult], $cumulativeInput, 0);
        $this->chatHistory->addMessage($toolResult2);

        // Large message that exceeds context window: 1200 total input tokens
        $largeMessage = $this->createMessageWithCumulativeUsage('Large message', 1200, 0);
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
        $this->assertLessThanOrEqual(1000, $this->chatHistory->calculateTotalUsage());
    }

    public function testRegularMessagesAreRemovedWhenContextWindowExceeded(): void
    {
        $cumulativeInput = 0;

        // Add several regular messages with cumulative input tokens
        for ($i = 0; $i < 5; $i++) {
            $cumulativeInput += 250; // Each message adds 250 more input tokens cumulatively
            $message = $this->createMessageWithCumulativeUsage("Message $i", $cumulativeInput, 0);
            $this->chatHistory->addMessage($message);
        }

        $remainingMessages = $this->chatHistory->getMessages();

        // With context window of 1000, we should have fewer than 5 messages
        $this->assertLessThan(5, \count($remainingMessages), 'Some messages should be removed due to context window limit');

        // Verify total usage is within context window
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

        // User message: 200 total input tokens
        $cumulativeInput = 200;
        $userMessage = $this->createMessageWithCumulativeUsage('User message', $cumulativeInput, 0);
        $this->chatHistory->addMessage($userMessage);

        // Assistant message: 350 total input tokens
        $cumulativeInput = 350;
        $assistantMessage = $this->createMessageWithCumulativeUsage('Assistant response', $cumulativeInput, 100, 'assistant');
        $this->chatHistory->addMessage($assistantMessage);

        // Tool call: 550 total input tokens
        $cumulativeInput = 550;
        $toolCall = $this->createToolCallWithCumulativeUsage('Tool call', [$tool], $cumulativeInput, 50);
        $this->chatHistory->addMessage($toolCall);

        // Tool result: 650 total input tokens
        $cumulativeInput = 650;
        $toolResult = $this->createToolResultWithCumulativeUsage([$toolWithResult], $cumulativeInput, 0);
        $this->chatHistory->addMessage($toolResult);

        // Large message that exceeds context: 1200 total input tokens
        $largeMessage = $this->createMessageWithCumulativeUsage('Very large message', 1200, 0);
        $this->chatHistory->addMessage($largeMessage);

        // Verify context window is respected
        $this->assertLessThanOrEqual(1000, $this->chatHistory->calculateTotalUsage());

        $messages = $this->chatHistory->getMessages();

        // Verify we still have some messages
        $this->assertGreaterThan(0, \count($messages));
    }

    public function testMarginalTokenCalculationIsCorrect(): void
    {
        // First message: 200 total input tokens (200 marginal)
        $cumulativeInput = 200;
        $message1 = $this->createMessageWithCumulativeUsage('First message', $cumulativeInput, 50);
        $this->chatHistory->addMessage($message1);
        $this->assertEquals(200, $message1->getUsage()->inputTokens, 'First message should have 200 marginal input tokens');

        // Second message: 350 total input tokens (150 marginal)
        $cumulativeInput = 350;
        $message2 = $this->createMessageWithCumulativeUsage('Second message', $cumulativeInput, 30);
        $this->chatHistory->addMessage($message2);
        $this->assertEquals(150, $message2->getUsage()->inputTokens, 'Second message should have 150 marginal input tokens');

        // Third message: 500 total input tokens (150 marginal)
        $cumulativeInput = 500;
        $message3 = $this->createMessageWithCumulativeUsage('Third message', $cumulativeInput, 20);
        $this->chatHistory->addMessage($message3);
        $this->assertEquals(150, $message3->getUsage()->inputTokens, 'Third message should have 150 marginal input tokens');

        // Total usage should be: (200+50) + (150+30) + (150+20) = 600
        $this->assertEquals(600, $this->chatHistory->calculateTotalUsage());
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

        // Regular message
        $cumulativeInput = 100;
        $regularMessage = $this->createMessageWithCumulativeUsage('Regular message', $cumulativeInput, 0);
        $this->chatHistory->addMessage($regularMessage);

        // First tool call
        $cumulativeInput = 200;
        $toolCall1 = $this->createToolCallWithCumulativeUsage('First tool call', [$tool1], $cumulativeInput, 0);
        $this->chatHistory->addMessage($toolCall1);

        // First tool result
        $cumulativeInput = 300;
        $toolResult1 = $this->createToolResultWithCumulativeUsage([$tool1WithResult], $cumulativeInput, 0);
        $this->chatHistory->addMessage($toolResult1);

        // Second tool call
        $cumulativeInput = 400;
        $toolCall2 = $this->createToolCallWithCumulativeUsage('Second tool call', [$tool2], $cumulativeInput, 0);
        $this->chatHistory->addMessage($toolCall2);

        // Second tool result
        $cumulativeInput = 500;
        $toolResult2 = $this->createToolResultWithCumulativeUsage([$tool2WithResult], $cumulativeInput, 0);
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
        // First message: 100 input + 50 output = 150 total
        $cumulativeInput = 100;
        $message1 = $this->createMessageWithCumulativeUsage('Test message 1', $cumulativeInput, 50);
        $this->chatHistory->addMessage($message1);

        // Second message: 200 input + 100 output = 300 total
        // But marginal input is 200 - 100 = 100, so total usage is 150 + 200 = 350
        $cumulativeInput = 200;
        $message2 = $this->createMessageWithCumulativeUsage('Test message 2', $cumulativeInput, 100, 'assistant');
        $this->chatHistory->addMessage($message2);

        // Total usage should be 150 + 200 = 350
        // Free memory should be 1000 - 350 = 650
        $this->assertEquals(650, $this->chatHistory->getFreeMemory());
    }

    public function testEmptyHistoryAfterFlushAll(): void
    {
        // Add some messages
        $this->chatHistory->addMessage($this->createMessageWithCumulativeUsage('Test message', 100, 0));
        $this->chatHistory->addMessage($this->createMessageWithCumulativeUsage('Test response', 200, 50, 'assistant'));

        $this->assertGreaterThan(0, \count($this->chatHistory->getMessages()));

        // Flush all messages
        $this->chatHistory->flushAll();

        $this->assertEmpty($this->chatHistory->getMessages());
        $this->assertEquals(0, $this->chatHistory->calculateTotalUsage());
        $this->assertEquals(1000, $this->chatHistory->getFreeMemory());
    }

    public function testHistoryKeysAreRecalculatedAfterCutting(): void
    {
        $cumulativeInput = 0;

        // Add messages that will exceed context window
        for ($i = 0; $i < 6; $i++) {
            $cumulativeInput += 200;
            $message = $this->createMessageWithCumulativeUsage("Message $i", $cumulativeInput, 0);
            $this->chatHistory->addMessage($message);
        }

        $messages = $this->chatHistory->getMessages();

        // Check that array keys are sequential (0, 1, 2, ...)
        $expectedKeys = \array_keys($messages);
        $actualKeys = \range(0, \count($messages) - 1);

        $this->assertEquals($actualKeys, $expectedKeys, 'Array keys should be sequential after cutting');
        $this->assertLessThanOrEqual(1000, $this->chatHistory->calculateTotalUsage());
    }
}
