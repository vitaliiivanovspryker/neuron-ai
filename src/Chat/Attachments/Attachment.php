<?php

namespace NeuronAI\Chat\Attachments;

class Attachment implements \JsonSerializable
{
    const DOCUMENT = 'document';
    const IMAGE = 'image';
    const TYPE_URL = 'url';
    const TYPE_BASE64 = 'base64';

    public function __construct(
        public string $attachment,
        public string $content,
        public string $type,
        public ?string $mediaType
    ) {}

    public function jsonSerialize(): array
    {
        return \array_filter([
            $this->attachment => $this->content,
            'type' => $this->type,
            'media_type' => $this->mediaType,
        ]);
    }
}
