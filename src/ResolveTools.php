<?php

namespace NeuronAI;

use NeuronAI\Exceptions\AgentException;
use NeuronAI\Tools\ToolInterface;
use NeuronAI\Tools\Toolkits\ToolkitInterface;

trait ResolveTools
{
    /**
     * Registered tools.
     *
     * @var ToolInterface[]
     */
    protected array $tools = [];

    /**
     * Get the list of tools.
     *
     * @return ToolInterface[]
     */
    protected function tools(): array
    {
        return $this->tools;
    }

    /**
     * @return ToolInterface[]
     */
    public function getTools(): array
    {
        return empty($this->tools)
            ? $this->tools()
            : $this->tools;
    }

    /**
     * @return ToolInterface[]
     */
    public function bootstrapTools(): array
    {
        $tools = [];
        $guidelines = [];

        foreach ($this->getTools() as $tool) {
            if ($tool instanceof ToolkitInterface) {
                if ($kitGuidelines = $tool->guidelines()) {
                    $guidelines[] = '# '.$tool::class.PHP_EOL.$kitGuidelines;
                }

                $tools = \array_merge($tools, $tool->tools());
            }
        }

        if (!empty($guidelines)) {
            $this->withInstructions(
                $this->instructions().PHP_EOL.'<TOOLS-GUIDELINES>'.implode(PHP_EOL, $guidelines).'</TOOLS-GUIDELINES>'
            );
        }

        return $tools;
    }

    /**
     * Add tools.
     *
     * @param ToolInterface|array $tool
     * @return AgentInterface
     * @throws AgentException
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
