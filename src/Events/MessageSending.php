<?php

namespace NeuronAI\Events;

use NeuronAI\Chat\Messages\AbstractMessage;

class MessageSending
{
    public function __construct(public AbstractMessage $message) {}
}
