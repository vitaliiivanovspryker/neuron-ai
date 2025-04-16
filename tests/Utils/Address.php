<?php

namespace NeuronAI\Tests\Utils;

use NeuronAI\StructuredOutput\SchemaProperty;
use Symfony\Component\Validator\Constraints\NotBlank;

class Address
{
    #[SchemaProperty(description: 'The name of the street')]
    #[NotBlank]
    public string $street;

    public string $city;

    #[SchemaProperty(description: 'The zip code of the address')]
    #[NotBlank]
    public string $zip;
}
