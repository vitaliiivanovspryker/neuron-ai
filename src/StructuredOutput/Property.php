<?php

namespace NeuronAI\StructuredOutput;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
class Property
{
    public function __construct(
        public ?string $title = null,
        public ?string $description = null
    ) {}
}
