<?php

namespace NeuronAI\Tests\Utils;

use Symfony\Component\Validator\Constraints as Assert;

class Person
{
    #[Assert\NotBlank()]
    public string $firstName;
    public string $lastName;
}
