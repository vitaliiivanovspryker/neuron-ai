<?php

namespace NeuronAI\Tools\Toolkits\Calculator;

use NeuronAI\Tools\PropertyType;
use NeuronAI\Tools\Tool;
use NeuronAI\Tools\ToolProperty;

class ExponentiateTool extends Tool
{
    protected string $name = 'exponentiate';
    protected string $description = 'Calculate the exponential between two numbers and return the result';

    public function properties(): array
    {
        return [
            ToolProperty::make(
                name: 'number',
                type: PropertyType::NUMBER,
                description: 'The base number to exponentiate',
                required: true,
            ),
            ToolProperty::make(
                name: 'exponent',
                type: PropertyType::NUMBER,
                description: 'The exponent',
                required: true,
            )
        ];
    }

    public function __invoke(int|float $number, int $exponent): int|float
    {
        return pow($number, $exponent);
    }
}
