<?php

namespace NeuronAI\Tools;

class ToolProperty implements ToolPropertyInterface
{
    public function __construct(
        protected string $name,
        protected array|PropertyType $type,
        protected string $description,
        protected bool $required = false,
        protected array $enum = [],
    ) {
        if (is_array($this->type)) {
            array_walk($this->type, fn ($item) => ($item instanceof PropertyType) ?: throw new \Exception("The type {$item} is not a valid property type."));
        }
    }

    /**
     * @return array
     */
    public function jsonSerialize(): array
    {
        return [
            'name' => $this->name,
            'description' => $this->description,
            'type' => $this->type,
            'enum' => $this->enum,
            'required' => $this->required,
        ];
    }

    public function isRequired(): bool
    {
        return $this->required;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getType(): PropertyType|array
    {
        return $this->type;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function getEnum(): array
    {
        return $this->enum;
    }
}
