<?php

namespace NeuronAI\Tools;

use NeuronAI\Chat\Messages\Message;

class ToolCall
{
    /**
     * @param array<Tool> $tools
     */
    public function __construct(
        protected array $tools,
        protected Message $message,
    ) {}

    /**
     * @return array<Tool>
     */
    public function getTools(): array
    {
        return $this->tools;
    }
}
