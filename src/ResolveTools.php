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

    public function bootstrapTools(): void
    {
        $tools = [];
        $guidelines = [];

        foreach ($this->getTools() as $tool) {
            if ($tool instanceof ToolkitInterface) {
                if ($kitGuidelines = $tool->guidelines()) {
                    $name = (new \ReflectionClass($tool))->getShortName();
                    $kitGuidelines = '# '.$name.PHP_EOL.$kitGuidelines;
                }

                $tools = \array_merge($tools, $tool->tools());

                if ($kitGuidelines) {
                    $kitGuidelines .= PHP_EOL.implode(
                        PHP_EOL.'- ',
                        \array_map(
                            fn ($tool) => "{$tool->getName()}: {$tool->getDescription()}",
                            $tools
                        )
                    );

                    $guidelines[] = $kitGuidelines;
                }
            }
        }

        if (!empty($guidelines)) {
            $instructions = $this->removeDelimitedContent($this->instructions(), '<TOOLS-GUIDELINES>', '</TOOLS-GUIDELINES>');
            $this->withInstructions(
                $instructions.PHP_EOL.'<TOOLS-GUIDELINES>'.PHP_EOL.implode(PHP_EOL.PHP_EOL, $guidelines).PHP_EOL.'</TOOLS-GUIDELINES>'
            );
        }

        $this->tools = $tools;
    }

    /**
     * Add tools.
     *
     * @param ToolInterface|ToolkitInterface|array $tool
     * @return AgentInterface
     * @throws AgentException
     */
    public function addTool(ToolInterface|ToolkitInterface|array $tool): AgentInterface
    {
        $tool = \is_array($tool) ? $tool : [$tool];

        foreach ($tool as $t) {
            if (! $t instanceof ToolInterface && ! $t instanceof ToolkitInterface) {
                throw new AgentException('Tool must be an instance of ToolInterface or ToolkitInterface');
            }
            $this->tools[] = $t;
        }

        return $this;
    }
}
