<?php

namespace NeuronAI\Tools\Toolkits\Tavily;

use NeuronAI\StaticConstructor;
use NeuronAI\Tools\Tool;
use NeuronAI\Tools\Toolkits\AbstractToolkit;
use NeuronAI\Tools\Toolkits\ToolkitInterface;

class TavilyToolkit extends AbstractToolkit
{
    public function __construct(protected string $key)
    {
    }

    /**
     * @return array<Tool>
     */
    public function tools(): array
    {
        return [
            new TavilyExtractTool($this->key),
            new TavilySearchTool($this->key),
        ];
    }
}
