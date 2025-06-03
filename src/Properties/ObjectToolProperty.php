<?php

namespace NeuronAI\Properties;

class ObjectToolProperty implements ToolPropertyInterface
{
    /**
     * @var string
     */
    protected string $type = 'object';

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
    )
    {
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
            'properties' => $this->makeItems(),
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

    /**
     * @return mixed
     */
    public function makeItems()
    {
        return \array_reduce($this->properties, function (array $carry, ToolPropertyInterface $property) {
            $carry[$property->getName()] = [
                'description' => $property->getDescription(),
                'type' => $property->getType(),
            ];

            if ($property instanceof ToolProperty && !empty($property->getEnum())) {
                $carry[$property->getName()]['enum'] = $property->getEnum();
            }

            if ($property instanceof ArrayToolProperty && !empty($property->getItems())) {
                $carry[$property->getName()]['items'] = [
                    'type' => 'object',
                    'properties' => $property->makeItems(),
                    'required' => $property->getRequiredProperties(),
                ];
            }

            if ($property instanceof ObjectToolProperty && !empty($property->getItems())) {
                $carry[$property->getName()]['properties'] = $property->makeItems();
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
     * @return string|array
     */
    public function getType(): string|array
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
