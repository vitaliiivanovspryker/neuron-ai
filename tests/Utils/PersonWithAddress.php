<?php

namespace NeuronAI\Tests\Utils;

use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Valid;

class PersonWithAddress
{
    #[NotBlank]
    public string $firstName;
    public string $lastName;

    #[NotBlank]
    #[Valid]
    public Address $address;
}
