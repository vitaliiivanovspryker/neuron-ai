<?php

namespace NeuronAI\Tools\Toolkits\Riza;

use NeuronAI\StaticConstructor;
use NeuronAI\Tools\Tool;
use NeuronAI\Tools\Toolkits\AbstractToolkit;
use NeuronAI\Tools\Toolkits\ToolkitInterface;

class RizaToolkit extends AbstractToolkit
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
            new RizaCodeInterpreter($this->key),
            new RizaFunctionExecutor($this->key),
        ];
    }
}
