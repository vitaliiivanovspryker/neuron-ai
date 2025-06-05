<?php

namespace NeuronAI\Tools\Toolkits\Zep;

use NeuronAI\Tools\Toolkits\ToolkitInterface;

class ZepToolkit implements ToolkitInterface
{
    public function __construct(protected string $key)
    {
    }

    public function tools(): array
    {
        return [];
    }

    public function getContext()
    {

    }
}
