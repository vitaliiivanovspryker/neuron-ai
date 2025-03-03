<?php

namespace NeuronAI\Events;

use NeuronAI\Messages\AbstractMessage;

class MessageSending
{
    public function __construct(public AbstractMessage $message) {}
}
