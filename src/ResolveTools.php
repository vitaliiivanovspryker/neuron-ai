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
    protected function tools(): array
    {
        return $this->tools;
    }

    /**
     * @return array<ToolInterface>
     */
    public function getTools(): array
    {
        return empty($this->tools)
            ? $this->tools()
            : $this->tools;
    }

    /**
     * Add a tool.
     *
     * @param ToolInterface $tool
     * @return AgentInterface
     */
    public function addTool(ToolInterface $tool): AgentInterface
    {
        $this->tools[] = $tool;
        return $this;
    }
}
