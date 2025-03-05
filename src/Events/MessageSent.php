<?php

namespace NeuronAI\Events;

use NeuronAI\Chat\Messages\AbstractMessage;

class MessageSent
{
    public function __construct(
        public AbstractMessage $message,
        public AbstractMessage $response
    ) {}
}
