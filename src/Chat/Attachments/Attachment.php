<?php

namespace NeuronAI\Chat\Attachments;

use NeuronAI\StaticConstructor;

class Attachment implements \JsonSerializable
{
    use StaticConstructor;

    public const DOCUMENT = 'document';
    public const IMAGE = 'image';
    public const TYPE_URL = 'url';
    public const TYPE_BASE64 = 'base64';

    public function __construct(
        public string $type,
        public string $content,
        public string $contentType,
        public ?string $mediaType
    ) {
        //
    }

    public function jsonSerialize(): array
    {
        return \array_filter([
            $this->type => $this->content,
            'type' => $this->contentType,
            'media_type' => $this->mediaType,
        ]);
    }
}
