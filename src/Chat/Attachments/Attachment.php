<?php

namespace NeuronAI\Chat\Attachments;

use NeuronAI\Chat\Enums\AttachmentContentType;
use NeuronAI\Chat\Enums\AttachmentType;
use NeuronAI\StaticConstructor;

class Attachment implements \JsonSerializable
{
    use StaticConstructor;

    public function __construct(
        public AttachmentType $type,
        public string $content,
        public AttachmentContentType $contentType,
        public ?string $mediaType
    ) {
        //
    }

    public function jsonSerialize(): array
    {
        return \array_filter([
            $this->type->value => $this->content,
            'type' => $this->contentType->value,
            'media_type' => $this->mediaType,
        ]);
    }
}
