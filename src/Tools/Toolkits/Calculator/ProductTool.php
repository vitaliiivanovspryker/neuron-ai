<?php

namespace NeuronAI\Tools\Toolkits\Calculator;

use NeuronAI\Tools\PropertyType;
use NeuronAI\Tools\Tool;
use NeuronAI\Tools\ToolProperty;

class ProductTool extends Tool
{
    public function __construct()
    {
        parent::__construct(
            'multiply_numbers',
            'Calculate the product of two numbers',
        );

        $this->addProperty(
            new ToolProperty(
                'a',
                PropertyType::NUMBER,
                'The first number of the product.',
                true
            )
        )->addProperty(
            new ToolProperty(
                'b',
                PropertyType::NUMBER,
                'The second number of the product.',
                true
            )
        )->setCallable(fn ($a, $b) => $a * $b);
    }
}
