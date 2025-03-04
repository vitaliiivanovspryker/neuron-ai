<?php

namespace NeuronAI\Events;

class InstructionsChanged
{
    public function __construct(
        public string $instructions
    ) {}
}
