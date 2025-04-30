<?php

namespace NeuronAI;

use NeuronAI\Exceptions\AgentException;
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
     * Add tools.
     *
     * @param ToolInterface|array<ToolInterface> $tool
     * @return AgentInterface
     */
    public function addTool(ToolInterface|array $tool): AgentInterface
    {
        $tool = \is_array($tool) ? $tool : [$tool];

        foreach ($tool as $t) {
            if (! $t instanceof ToolInterface) {
                throw new AgentException('Tool must be an instance of ToolInterface');
            }
            $this->tools[] = $t;
        }

        return $this;
    }
}
