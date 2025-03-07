<?php

namespace NeuronAI\Chat\Messages;

class AssistantMessage extends Message
{
    public function __construct(mixed $content)
    {
        parent::__construct(Message::ROLE_ASSISTANT, $content);
    }
}
