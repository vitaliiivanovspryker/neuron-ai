<?php

namespace NeuronAI\StructuredOutput\Validation\Rules;

#[\Attribute(\Attribute::TARGET_PROPERTY | \Attribute::IS_REPEATABLE)]
class NotEqualTo extends AbstractValidationRule
{
    public function __construct(protected mixed $compareTo)
    {
    }

    public function validate(string $name, mixed $value, array &$violations)
    {
        if ($value === $this->compareTo) {
            $violations[] = $this->buildMessage($name, 'must not be equal to {compare}', ['compare' => get_debug_type($this->compareTo)]);
        }
    }
}
