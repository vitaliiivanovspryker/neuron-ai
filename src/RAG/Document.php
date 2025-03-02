<?php

namespace NeuronAI\RAG;

class Document
{
    public mixed $id;

    /** @var float[]|null */
    public ?array $embedding = null;

    public string $sourceType = 'manual';

    public string $sourceName = 'manual';

    public string $hash = '';

    public int $chunkNumber = 0;

    public function __construct(
        public string $content,
    ) {}
}
