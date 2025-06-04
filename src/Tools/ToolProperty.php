<?php

namespace NeuronAI\Tools;

class ToolProperty implements ToolPropertyInterface
{
    public const TP_INTEGER = 'int';
    public const TP_STRING = 'string';
    public const TP_NUMBER = 'number';
    public const TP_NULLABLE = 'null';

    public function __construct(
        protected string $name,
        protected string|array $type,
        protected string $description,
        protected bool $required = false,
        protected array $enum = [],
    ) {
        $this->ensureTypeValidation($type);
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

    public function getType(): string|array
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

    private function ensureTypeValidation(string|array $type): void
    {
        $types = is_array($type) ? $type : [$type];
        $validTypes = [
            self::TP_INTEGER,
            self::TP_STRING,
            self::TP_NUMBER,
            self::TP_NULLABLE
        ];

        foreach ($types as $type) {
            if (!in_array($type, $validTypes, true)) {
                throw new \InvalidArgumentException(
                    sprintf(
                        'The type "%s" is not valid. Valid types are: %s',
                        $type,
                        implode(', ', $validTypes)
                    )
                );
            }
        }
    }
}
