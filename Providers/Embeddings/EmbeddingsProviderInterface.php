<?php

namespace App\Extensions\NeuronAI\Providers\Embeddings;

use App\Extensions\NeuronAI\RAG\Document;

interface EmbeddingsProviderInterface
{
    /**
     * @return float[]
     */
    public function embedText(string $text): array;

    public function embedDocument(Document $document): Document;
}
