<?php

namespace NeuronAI\Tools;

class Tool implements ToolInterface
{
    /**
     * The list of callback function arguments.
     *
     * @var array<ToolProperty>
     */
    protected array $properties = [];

    /**
     * @var callable
     */
    protected $callback;

    /**
     * Tool constructor.
     *
     * @param string $name
     * @param string $description
     */
    public function __construct(
        protected string $name,
        protected string $description,
    ) {}

    public static function make(...$args): static
    {
        return new static(...$args);
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function addProperty(ToolProperty $property): ToolInterface {
        $this->properties[] = $property;
        return $this;
    }

    public function getProperties(): array
    {
        return $this->properties;
    }

    public function getRequiredProperties(): array
    {
        return \array_filter(\array_map(function (ToolProperty $property) {
            return $property->isRequired() ? $property->getName() : null;
        }, $this->properties));
    }

    public function setCallable(callable $callback): self
    {
        $this->callback = $callback;
        return $this;
    }

    /**
     * Execute the client side function.
     *
     * @param array $input
     * @return mixed
     */
    public function execute(array $input): mixed
    {
        if (!isset($this->callback)) {
            throw new \BadMethodCallException('No callback defined for execution.');
        }

        // Validate required parameters
        foreach ($this->properties as $property) {
            if ($property->isRequired() && ! \array_key_exists($property->getName(), $input)) {
                throw new \InvalidArgumentException("Missing required parameter: {$property->getName()}");
            }
        }

        // Execute the callback with input
        return \call_user_func($this->callback, ...$input);
    }
}
