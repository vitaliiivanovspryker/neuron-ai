<?php

namespace NeuronAI\Tests\Utils;

use NeuronAI\StructuredOutput\SchemaProperty;
use Symfony\Component\Validator\Constraints\NotBlank;

class Tag
{
    #[SchemaProperty(description: 'The name of the tag')]
    #[NotBlank]
    public string $name;
}
