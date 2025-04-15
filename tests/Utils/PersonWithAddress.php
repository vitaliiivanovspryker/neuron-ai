<?php

namespace NeuronAI\Tests\Utils;

use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Valid;

class PersonWithAddress
{
    #[NotBlank]
    public string $firstName;
    public string $lastName;

    #[Valid]
    public Address $address;
}
