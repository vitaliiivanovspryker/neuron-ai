<?php

namespace NeuronAI\Chat\Messages;

class ToolCallResultMessage extends Message
{
    public function __construct(protected array $tools)
    {
        parent::__construct();
    }

    public function getTools(): array
    {
        return $this->tools;
    }
}
