<?php

namespace NeuronAI\Events;

use NeuronAI\Tools\ToolCallMessage;

class ToolCalled
{
    public function __construct(
        public ToolCallMessage $toolCall,
        public mixed $result
    ) {}
}
