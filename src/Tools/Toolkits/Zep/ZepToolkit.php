<?php

namespace NeuronAI\Tools\Toolkits\Zep;

use NeuronAI\StaticConstructor;
use NeuronAI\Tools\Toolkits\ToolkitInterface;

class ZepToolkit implements ToolkitInterface
{
    use StaticConstructor;

    public function __construct(protected string $key)
    {
    }

    public function tools(): array
    {
        return [
            ZepGetMemoryTool::make($this->key),
            ZepAddMemoryTool::make($this->key),
        ];
    }
}
