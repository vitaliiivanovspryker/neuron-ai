<?php

namespace NeuronAI\Providers;

use NeuronAI\Chat\Messages\Message;

interface MessageMapperInterface
{
    public function map(Message $message): array;
}
