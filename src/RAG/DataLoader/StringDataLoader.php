<?php

namespace NeuronAI\RAG\DataLoader;

use NeuronAI\RAG\Document;
use NeuronAI\RAG\Splitters\DocumentSplitter;

class StringDataLoader implements DataLoaderInterface
{
    public function __construct(protected string $content) {}

    public function getDocuments(): array
    {
        return DocumentSplitter::splitDocument(new Document($this->content));
    }
}
