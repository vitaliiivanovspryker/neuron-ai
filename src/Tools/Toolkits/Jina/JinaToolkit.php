<?php

namespace NeuronAI\Tools\Toolkits\Jina;

use NeuronAI\StaticConstructor;
use NeuronAI\Tools\Tool;
use NeuronAI\Tools\Toolkits\AbstractToolkit;
use NeuronAI\Tools\Toolkits\ToolkitInterface;

class JinaToolkit extends AbstractToolkit
{
    use StaticConstructor;

    public function __construct(protected string $key)
    {
    }

    /**
     * @return array<Tool>
     */
    public function tools(): array
    {
        return [
            new JinaWebSearch($this->key),
            new JinaUrlReader($this->key),
        ];
    }
}
