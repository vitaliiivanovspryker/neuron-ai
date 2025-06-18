<?php

namespace NeuronAI\Tests\Stubs\Tools;

use NeuronAI\Tools\PropertyType;
use NeuronAI\Tools\ToolProperty;

class TestToolClassWithParentConstructorMixed extends \NeuronAI\Tools\Tool
{
    public function __construct(protected string $key, protected bool $secondProperty = false)
    {
        parent::__construct('test_tool', 'test tool');

        if ($this->secondProperty) {
            $this->addProperty(new ToolProperty('param', PropertyType::STRING, 'the param'));
        }
    }

    public function properties(): array
    {
        return [
            new ToolProperty(
                'url',
                PropertyType::STRING,
                'The URL to read.',
                true
            ),
        ];
    }

    public function getKey(): string
    {
        return $this->key;
    }
}
