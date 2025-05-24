<?php

namespace NeuronAI\Tools\Toolkits;

use NeuronAI\Tools\Tool;

interface ToolkitInterface
{
    /**
     * @return array<Tool>
     */
    public function tools(): array;
}
