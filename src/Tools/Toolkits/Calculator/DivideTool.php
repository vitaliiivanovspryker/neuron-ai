<?php

namespace NeuronAI\Tools\Toolkits\Calculator;

use NeuronAI\Tools\PropertyType;
use NeuronAI\Tools\Tool;
use NeuronAI\Tools\ToolProperty;

class DivideTool extends Tool
{
    public function __construct()
    {
        parent::__construct(
            'divide_numbers',
            'Calculate the division between two numbers',
        );

        $this->addProperty(
            new ToolProperty(
                'a',
                PropertyType::NUMBER,
                'The numerator of the division.',
                true
            )
        )->addProperty(
            new ToolProperty(
                'b',
                PropertyType::NUMBER,
                'The denominator of the division.',
                true
            )
        )->setCallable(fn ($a, $b) => $a / $b);
    }
}
