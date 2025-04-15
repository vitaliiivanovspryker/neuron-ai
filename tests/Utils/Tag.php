<?php

namespace NeuronAI\Tests\Utils;

use NeuronAI\StructuredOutput\Property;
use Symfony\Component\Validator\Constraints\NotBlank;

class Tag
{
    #[Property(description: 'The name of the tag')]
    #[NotBlank]
    public string $name;
}
