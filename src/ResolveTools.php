<?php

namespace NeuronAI;

use NeuronAI\Exceptions\AgentException;
use NeuronAI\Observability\Events\ToolsBootstrapped;
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
        return [];
    }

    /**
     * @return ToolInterface[]
     */
    public function getTools(): array
    {
        $agentTools = $this->tools();
        $runtimeTools = $this->tools;

        return \array_merge($runtimeTools, $agentTools);
    }

    /**
     * If toolkits have already bootstrapped, this function
     * just traverses the array of tools without any action.
     *
     * @return ToolInterface[]
     */
    public function bootstrapTools(): array
    {
        $bootstrapped = [];
        $guidelines = [];

        $this->notify('toolkits-bootstrapping');

        foreach ($this->getTools() as $tool) {
            if ($tool instanceof ToolkitInterface) {
                if ($kitGuidelines = $tool->guidelines()) {
                    $name = (new \ReflectionClass($tool))->getShortName();
                    $kitGuidelines = '# '.$name.PHP_EOL.$kitGuidelines;
                }

                // Merge the tools
                $innerTools = $tool->tools();
                $bootstrapped = \array_merge($bootstrapped, $innerTools);

                // Add guidelines to the system prompt
                if ($kitGuidelines) {
                    $kitGuidelines .= PHP_EOL.implode(
                        PHP_EOL.'- ',
                        \array_map(
                            fn ($tool) => "{$tool->getName()}: {$tool->getDescription()}",
                            $innerTools
                        )
                    );

                    $guidelines[] = $kitGuidelines;
                }
            } else {
                // If the item is a simple tool, add to the list as it is
                $bootstrapped[] = $tool;
            }
        }

        if (!empty($guidelines)) {
            $instructions = $this->removeDelimitedContent($this->instructions(), '<TOOLS-GUIDELINES>', '</TOOLS-GUIDELINES>');
            $this->withInstructions(
                $instructions.PHP_EOL.'<TOOLS-GUIDELINES>'.PHP_EOL.implode(PHP_EOL.PHP_EOL, $guidelines).PHP_EOL.'</TOOLS-GUIDELINES>'
            );
        }

        $this->notify('toolkits-bootstrapped', new ToolsBootstrapped($bootstrapped));

        return $bootstrapped;
    }

    /**
     * Add tools.
     *
     * @param ToolInterface|ToolkitInterface|array $tools
     * @return AgentInterface
     * @throws AgentException
     */
    public function addTool(ToolInterface|ToolkitInterface|array $tools): AgentInterface
    {
        $tools = \is_array($tools) ? $tools : [$tools];

        foreach ($tools as $t) {
            if (! $t instanceof ToolInterface && ! $t instanceof ToolkitInterface) {
                throw new AgentException('Tools must be an instance of ToolInterface or ToolkitInterface');
            }
            $this->tools[] = $t;
        }

        return $this;
    }
}
