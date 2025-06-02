<?php

namespace NeuronAI\RAG;

class Document implements \JsonSerializable
{
    public mixed $id;

    /** @var float[]|null */
    public ?array $embedding = null;

    public string $sourceType = 'manual';

    public string $sourceName = 'manual';

    public ?string $hash = null;

    public int $chunkNumber = 0;

    public float $score = 0;

    public function __construct(
        public string $content = '',
    ) {
    }

    public function jsonSerialize(): array
    {
        return [
            'id' => $this->id,
            'hash' => $this->hash,
            'content' => $this->content,
            'embedding' => $this->embedding,
            'sourceType' => $this->sourceType,
            'sourceName' => $this->sourceName,
            'chunkNumber' => $this->chunkNumber,
            'score' => $this->score,
        ];
    }
}
