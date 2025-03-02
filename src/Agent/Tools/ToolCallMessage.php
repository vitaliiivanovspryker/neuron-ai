<?php

namespace App\Extensions\NeuronAI\Agent\Tools;

use App\Extensions\NeuronAI\Agent\Messages\AssistantMessage;

class ToolCallMessage extends AssistantMessage
{
    /**
     * @param Tool $tool
     * @param array $inputs
     */
    public function __construct(protected Tool $tool, protected array $inputs = [])
    {
        parent::__construct('');
    }

    public function getTool(): Tool
    {
        return $this->tool;
    }

    public function getInputs(): array
    {
        return $this->inputs;
    }
}
