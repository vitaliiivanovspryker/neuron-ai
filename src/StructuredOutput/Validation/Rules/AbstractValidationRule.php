<?php

namespace NeuronAI\StructuredOutput\Validation\Rules;

use NeuronAI\StructuredOutput\Validation\ValidationRuleInterface;

abstract class AbstractValidationRule implements ValidationRuleInterface
{
    protected function buildMessage(string $name, string $messageTemplate)
    {
        return \str_replace('{name}', $name, $messageTemplate);
    }
}
