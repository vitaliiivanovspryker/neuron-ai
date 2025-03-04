<?php

namespace NeuronAI\Events;

use NeuronAI\RAG\Document;

class VectorStoreResult
{
    /**
     * @param array<Document> $documents
     */
    public function __construct(
        public string $question,
        public array $documents,
    ) {}
}
