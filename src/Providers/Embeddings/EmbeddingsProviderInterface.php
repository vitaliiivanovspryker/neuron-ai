<?php

namespace NeuronAI\Providers\Embeddings;

use NeuronAI\RAG\Document;

interface EmbeddingsProviderInterface
{
    /**
     * @return float[]
     */
    public function embedText(string $text): array;

    public function embedDocument(Document $document): Document;
}
