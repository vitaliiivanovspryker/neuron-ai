<?php

namespace NeuronAI\Tests;

use NeuronAI\Tools\Tool;
use NeuronAI\Tools\ToolInterface;
use PHPUnit\Framework\TestCase;

class ToolTest extends TestCase
{
    public function test_tool_instance()
    {
        $tool = new Tool('example', 'example');
        $this->assertInstanceOf(ToolInterface::class, $tool);

        $tool->setInputs(null);
        $this->assertEquals([], $tool->getInputs());
    }

    public function test_required_properties()
    {
        $tool = Tool::make('test', 'Test tool')
            ->addProperty(
                new \NeuronAI\Tools\ToolProperty('name', 'string', 'User name', true)
            )
            ->addProperty(
                new \NeuronAI\Tools\ToolProperty('surname', 'string', 'User surname', false)
            )
            ->addProperty(
                new \NeuronAI\Tools\ToolProperty('age', 'integer', 'User age', true)
            )
            ->setCallable(function () {});

        $this->assertIsArray($tool->getRequiredProperties());
        $this->assertEquals(['name', 'age'], $tool->getRequiredProperties());
    }
}
