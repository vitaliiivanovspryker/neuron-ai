<?php

namespace NeuronAI\Chat\Messages;

class Image
{
    public function __construct(public string $image, public string $type = 'url', public ?string $mediaType = null)
    {
        //
    }
}
