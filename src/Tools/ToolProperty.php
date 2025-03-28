<?php

namespace NeuronAI\Tools;

class ToolProperty implements \JsonSerializable
{
    public function __construct(
        protected string $name,
        protected string|array $type,
        protected string $description,
        protected bool $required = false,
    ) {}

    /**
     * @return array
     */
    public function jsonSerialize(): array
    {
        return [
            'name' => $this->name,
            'type' => $this->type,
            'description' => $this->description,
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

    public function getType(): string|array
    {
        return $this->type;
    }

    public function getDescription(): string
    {
        return $this->description;
    }
}
