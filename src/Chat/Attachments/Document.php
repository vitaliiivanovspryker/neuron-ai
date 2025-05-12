<?php

namespace NeuronAI\Chat\Attachments;

class Document extends Attachment
{
    public function __construct(
        string $content,
        string $type = self::TYPE_URL,
        $mediaType = null
    ) {
        parent::__construct(
            Attachment::DOCUMENT,
            $content,
            $type,
            $mediaType
        );
    }
}
