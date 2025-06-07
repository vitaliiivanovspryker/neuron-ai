<?php

namespace NeuronAI\Tools\Toolkits\Calculator;

use NeuronAI\Tools\PropertyType;
use NeuronAI\Tools\Tool;
use NeuronAI\Tools\ToolProperty;

class SubtractTool extends Tool
{
    public function __construct()
    {
        parent::__construct(
            'subtract_numbers',
            'Calculate the subtraction between two numbers',
        );

        $this->addProperty(
            new ToolProperty(
                'a',
                PropertyType::NUMBER,
                'The first number of the subtraction.',
                true
            )
        )->addProperty(
            new ToolProperty(
                'b',
                PropertyType::NUMBER,
                'The second number of the subtraction.',
                true
            )
        )->setCallable(fn ($a, $b) => $a - $b);
    }
}
