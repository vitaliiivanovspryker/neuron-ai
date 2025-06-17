<?php

namespace NeuronAI\StructuredOutput\Validation\Rules;

use NeuronAI\StructuredOutput\StructuredOutputException;

#[\Attribute(\Attribute::TARGET_PROPERTY | \Attribute::IS_REPEATABLE)]
class Choice extends AbstractValidationRule
{
    protected string $message = '{name} must be one of the following allowed values: {choices}.';

    /**
     * @param array|null $choices List of allowed values. Cannot be used with `enum`.
     * @param string|null $enum Enum class name to extract choices from. Cannot be used with `choices`.
     *
     * @throws StructuredOutputException if both `choices` and `enum` are provided or if `enum` is not valid.
     */
    public function __construct(
        protected ?array $choices = [],
        protected ?string $enum = null,
    ) {
        if (!empty($this->choices) && $this->enum !== null) {
            throw new StructuredOutputException('You cannot provide both "choices" and "enum" options simultaneously. Please use only one.');
        }

        if (empty($this->choices) && $this->enum === null) {
            throw new StructuredOutputException('Either option "choices" or "enum" must be given for validation rule "Choice"');
        }

        if (empty($this->choices)) {
            $this->handleEnum();
        }
    }

    public function validate(string $name, mixed $value, array &$violations)
    {
        $value = $value instanceof \BackedEnum ? $value->value : $value;

        if (!in_array($value, $this->choices, true)) {
            $violations[] = $this->buildMessage($name, $this->message, ['choices' => implode(", ", $this->choices)]);
        }
    }

    /**
     * @throws StructuredOutputException
     */
    private function handleEnum(): void
    {
        if (!enum_exists($this->enum)) {
            throw new StructuredOutputException("Enum '{$this->enum}' does not exist.");
        }

        if (!is_subclass_of($this->enum, \BackedEnum::class)) {
            throw new StructuredOutputException("Enum '{$this->enum}' must implement BackedEnum.");
        }

        $this->choices = array_map(function (\BackedEnum $case) {
            return $case->value;
        }, $this->enum::cases());
    }
}
