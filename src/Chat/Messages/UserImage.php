<?php

namespace NeuronAI\Chat\Messages;

class UserImage extends Message
{
    public function __construct(string $image, string $type = 'url', string $mediaType = null)
    {
        parent::__construct(Message::ROLE_USER, [
            'image' => $image,
            'type' => $type,
            'media_type' => $mediaType,
        ]);
    }
}
