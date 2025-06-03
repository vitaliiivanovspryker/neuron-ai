<?php

namespace NeuronAI\StructuredOutput\Validation\Rules;

#[\Attribute(\Attribute::TARGET_PROPERTY | \Attribute::IS_REPEATABLE)]
class LowerThanEqual extends AbstractValidationRule
{
    public function __construct(protected mixed $compareTo)
    {
    }

    public function validate(string $name, mixed $value, array &$violations)
    {
        if (is_null($value) || $value > $this->compareTo) {
            $violations[] = $this->buildMessage($name, 'must be greater than {compare}', ['compare' => get_debug_type($this->compareTo)]);
        }
    }
}
