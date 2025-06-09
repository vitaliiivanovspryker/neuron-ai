<?php

namespace NeuronAI\Tools\Toolkits\Calculator;

use NeuronAI\Tools\PropertyType;
use NeuronAI\Tools\Tool;
use NeuronAI\Tools\ToolProperty;

class SumTool extends Tool
{
    public function __construct()
    {
        parent::__construct(
            'sum_numbers',
            'Calculate the sum of two numbers',
        );

        $this->addProperty(
            new ToolProperty(
                'a',
                PropertyType::NUMBER,
                'The first number of the sum.',
                true
            )
        )->addProperty(
            new ToolProperty(
                'b',
                PropertyType::NUMBER,
                'The second number of the sum.',
                true
            )
        )->setCallable(fn ($a, $b) => $a + $b);
    }
}
