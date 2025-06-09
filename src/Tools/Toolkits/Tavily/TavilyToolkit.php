<?php

namespace NeuronAI\Tools\Toolkits\Tavily;

use NeuronAI\Tools\Tool;
use NeuronAI\Tools\Toolkits\AbstractToolkit;

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
