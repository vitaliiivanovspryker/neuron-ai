<?php

namespace NeuronAI\Tools;

class ObjectProperty implements ToolPropertyInterface
{
    protected PropertyType $type = PropertyType::OBJECT;

    public function __construct(
        protected string $name,
        protected string $description,
        protected bool   $required = false,
        protected array  $properties = [],
    ) {
    }

    public function jsonSerialize(): array
    {
        return [
            'name' => $this->name,
            'description' => $this->description,
            'type' => $this->type,
            'properties' => $this->getJsonSchema(),
            'required' => $this->required,
        ];
    }

    public function getRequiredProperties(): array
    {
        return array_values(\array_filter(\array_map(function (ToolPropertyInterface $property) {
            return $property->isRequired() ? $property->getName() : null;
        }, $this->properties)));
    }

    public function getJsonSchema(): array
    {
        $schema = [
            'type' => $this->type->value,
            'description' => $this->description,
        ];

        $properties = \array_reduce($this->properties, function (array $carry, ToolPropertyInterface $property) {
            $carry[$property->getName()] = $property->getJsonSchema();
            return $carry;
        }, []);

        if (!empty($properties)) {
            $schema['properties'] = $properties;
            $schema['required'] = $this->getRequiredProperties();
        }

        return $schema;
    }

    public function isRequired(): bool
    {
        return $this->required;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getType(): PropertyType
    {
        return $this->type;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function getProperties(): array
    {
        return $this->properties;
    }
}
