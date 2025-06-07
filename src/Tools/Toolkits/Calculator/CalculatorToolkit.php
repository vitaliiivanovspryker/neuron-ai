<?php

namespace NeuronAI\Tools\Toolkits\Calculator;

use NeuronAI\Tools\Toolkits\ToolkitInterface;

class CalculatorToolkit implements ToolkitInterface
{
    public function tools(): array
    {
        return [
            SumTool::make(),
            SubtractTool::make(),
            ProductTool::make(),
            DivideTool::make(),
        ];
    }
}
