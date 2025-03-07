<?php

namespace NeuronAI\Tools;

use NeuronAI\Chat\Messages\Message;

class ToolCallMessage extends Message
{
    /**
     * @param array<Tool> $tools
     */
    public function __construct(protected array $tools) {
        parent::__construct();
    }

    /**
     * @return array<Tool>
     */
    public function getTools(): array
    {
        return $this->tools;
    }
}
