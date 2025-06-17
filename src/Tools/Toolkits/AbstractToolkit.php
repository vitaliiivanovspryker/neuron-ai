<?php

namespace NeuronAI\Tools\Toolkits;

use NeuronAI\Shared\Helper;
use NeuronAI\StaticConstructor;
use NeuronAI\Tools\ToolInterface;

abstract class AbstractToolkit implements ToolkitInterface
{
    use StaticConstructor;

    protected array $exclude = [];
    protected array $only = [];

    public function guidelines(): ?string
    {
        return null;
    }

    /**
     * @param  class-string[]  $classes
     */
    public function exclude(array $classes): ToolkitInterface
    {
        $this->exclude = $classes;
        return $this;
    }

    /**
     * @param  class-string[]  $classes
     */
    public function only(array $classes): ToolkitInterface
    {
        $this->only = $classes;
        return $this;
    }

    /**
     * @return ToolInterface[]
     */
    abstract public function provide(): array;

    public function tools(): array
    {
        return \array_filter($this->provide(),
            fn(ToolInterface $tool) => !in_array($tool::class, $this->exclude)
                && (empty($this->only) || in_array($tool::class, $this->only))
        );
    }
}
