<?php

namespace NeuronAI\Chat\Messages;

class UserImage extends Message
{
    public function __construct(public string $image, public string $type = 'url', public ?string $mediaType = null)
    {
        parent::__construct(Message::ROLE_USER);
    }
}
