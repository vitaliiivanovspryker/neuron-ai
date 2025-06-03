<?php

namespace NeuronAI\Tests\Utils;

use NeuronAI\StructuredOutput\Validation\Rules\NotBlank;

class Person
{
    #[NotBlank]
    public string $firstName;
    public string $lastName;
}
