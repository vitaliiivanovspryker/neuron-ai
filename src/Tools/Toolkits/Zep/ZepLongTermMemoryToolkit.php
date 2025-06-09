<?php

namespace NeuronAI\Tools\Toolkits\Zep;

use NeuronAI\StaticConstructor;
use NeuronAI\Tools\Toolkits\AbstractToolkit;
use NeuronAI\Tools\Toolkits\ToolkitInterface;

class ZepLongTermMemoryToolkit extends AbstractToolkit
{
    public function __construct(
        protected string $key,
        protected string $user_id,
    ) {
    }

    public function tools(): array
    {
        return [
            ZepSearchGraphTool::make($this->key, $this->user_id),
            ZepAddToGraphTool::make($this->key, $this->user_id),
        ];
    }
}
