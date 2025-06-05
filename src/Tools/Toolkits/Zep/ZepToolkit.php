<?php

namespace NeuronAI\Tools\Toolkits\Zep;

use NeuronAI\StaticConstructor;
use NeuronAI\Tools\Toolkits\ToolkitInterface;

class ZepToolkit implements ToolkitInterface
{
    use StaticConstructor;

    public function __construct(
        protected string $key,
        protected string $user_id,
        protected ?string $session_id = null,
    ) {
        if (is_null($this->session_id)) {
            $this->session_id = \uniqid();
        }
    }

    public function tools(): array
    {
        return [
            ZepGetMemoryTool::make($this->key, $this->user_id, $this->session_id),
            ZepAddMemoryTool::make($this->key, $this->user_id, $this->session_id),
        ];
    }
}
