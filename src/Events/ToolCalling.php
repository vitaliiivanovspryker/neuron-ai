<?php

namespace NeuronAI\Events;

use NeuronAI\Tools\ToolCallMessage;

class ToolCalling
{
    public function __construct(public ToolCallMessage $toolCall) {}
}
