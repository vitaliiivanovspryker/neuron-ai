<?php

namespace NeuronAI\Events;

use NeuronAI\Messages\Message;

class VectorStoreSearching
{
    public function __construct(
        public Message $question
    ) {}
}
