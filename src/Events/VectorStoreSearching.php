<?php

namespace NeuronAI\Events;

class VectorStoreSearching
{
    public function __construct(
        public string $question
    ) {}
}
