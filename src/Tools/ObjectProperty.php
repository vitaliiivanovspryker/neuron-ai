<?php

namespace NeuronAI\Tools;

class ObjectProperty implements ToolPropertyInterface
{
    protected PropertyType $type = PropertyType::OBJECT;

    /**
     * @param string $name
     * @param string $description
     * @param bool $required
     * @param array $properties
     */
    public function __construct(
        protected string $name,
        protected string $description,
        protected bool   $required = false,
        protected array  $properties = [],
    ) {
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
            'properties' => $this->makeItemsSchema(),
            'required' => $this->required,
        ];
    }

    /**
     * @return array
     */
    public function getRequiredProperties(): array
    {
        return array_values(\array_filter(\array_map(function (ToolPropertyInterface $property) {
            return $property->isRequired() ? $property->getName() : null;
        }, $this->properties)));
    }

    public function makeItemsSchema(): array
    {
        return \array_reduce($this->properties, function (array $carry, ToolPropertyInterface $property) {
            $carry[$property->getName()] = [
                'description' => $property->getDescription(),
                'type' => $property->getType()->value,
            ];

            if ($property instanceof ToolProperty && !empty($property->getEnum())) {
                $carry[$property->getName()]['enum'] = $property->getEnum();
            }

            if ($property instanceof ArrayProperty && !empty($property->getItems())) {
                $carry[$property->getName()]['items'] = [
                    'type' => 'object',
                    'properties' => $property->makeItemsSchema(),
                    'required' => $property->getRequiredProperties(),
                ];
            }

            if ($property instanceof ObjectProperty && !empty($property->getItems())) {
                $carry[$property->getName()]['properties'] = $property->makeItemsSchema();
                $carry[$property->getName()]['required'] = $property->getRequiredProperties();
            }

            return $carry;
        }, []);
    }

    /**
     * @return bool
     */
    public function isRequired(): bool
    {
        return $this->required;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return PropertyType|array
     */
    public function getType(): PropertyType|array
    {
        return $this->type;
    }

    /**
     * @return string
     */
    public function getDescription(): string
    {
        return $this->description;
    }

    /**
     * @return array
     */
    public function getItems(): array
    {
        return $this->properties;
    }
}
