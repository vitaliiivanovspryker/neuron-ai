<?php

namespace NeuronAI\RAG\DataLoader;

use NeuronAI\RAG\Document;

class StringDataLoader extends AbstractDataLoader
{
    public function __construct(protected string $content)
    {
    }

    public function getDocuments(): array
    {
        return DocumentSplitter::splitDocument(
            new Document($this->content),
            $this->maxLength,
            $this->separator,
            $this->wordOverlap
        );
    }
}
