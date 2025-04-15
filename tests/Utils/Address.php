<?php

namespace NeuronAI\Tests\Utils;

use NeuronAI\StructuredOutput\Property;
use Symfony\Component\Validator\Constraints\NotBlank;

class Address
{
    #[Property(description: 'The name of the street')]
    #[NotBlank]
    public string $street;

    public string $city;

    #[Property(description: 'The zip code of the address')]
    #[NotBlank]
    public string $zip;
}
