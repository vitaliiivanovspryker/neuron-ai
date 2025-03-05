<?php

namespace NeuronAI;

use NeuronAI\Tools\ToolInterface;

trait ResolveTools
{
    /**
     * Registered tools.
     *
     * @var array<ToolInterface>
     */
    protected array $tools = [];

    /**
     * Get the list of tools.
     *
     * @return array<ToolInterface>
     */
    public function tools(): array
    {
        return $this->tools;
    }

    /**
     * Add a tool.
     *
     * @param ToolInterface $tool
     * @return $this
     */
    public function addTool(ToolInterface $tool): self
    {
        $this->tools[] = $tool;
        return $this;
    }
}
