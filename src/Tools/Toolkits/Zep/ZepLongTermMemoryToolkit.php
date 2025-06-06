<?php

namespace NeuronAI\Tools\Toolkits\Zep;

use NeuronAI\StaticConstructor;
use NeuronAI\Tools\Toolkits\ToolkitInterface;

class ZepLongTermMemoryToolkit implements ToolkitInterface
{
    use StaticConstructor;

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
