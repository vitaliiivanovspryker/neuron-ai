<?php

namespace NeuronAI\Tools\Toolkits;

use NeuronAI\Tools\ToolInterface;

abstract class AbstractToolkit implements ToolkitInterface
{
    protected array $exclude;

    public function guidelines(): ?string
    {
        return null;
    }

    /**
     * @param string[] $classes
     * @return ToolkitInterface
     */
    public function exclude(array $classes): ToolkitInterface
    {
        $this->exclude = $classes;
        return $this;
    }

    public function tools(): array
    {
        return \array_filter($this->tools(), fn(ToolInterface $tool) => in_array($tool::class, $this->exclude));
    }
}
