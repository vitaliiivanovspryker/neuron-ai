<?php

namespace NeuronAI\Tests\Utils;

use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Valid;

class PersonWithTags
{
    #[NotBlank]
    public string $firstName;
    public string $lastName;

    /**
     * @var array<\NeuronAI\Tests\Utils\Tag>
     */
    #[Valid]
    #[NotBlank]
    public array $tags;
}
