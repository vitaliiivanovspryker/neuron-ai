<?php

namespace NeuronAI\Providers\Embeddings;

use NeuronAI\RAG\Document;

abstract class AbstractEmbeddingProvider implements EmbeddingsProviderInterface
{
    public function embedDocuments(array $documents): array
    {
        /** @var Document $document */
        foreach ($documents as $index => $document) {
            $documents[$index] = $this->embedDocument($document);
        }

        return $documents;
    }
}
