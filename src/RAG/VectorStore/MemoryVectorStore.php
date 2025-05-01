<?php

namespace NeuronAI\RAG\VectorStore;

use NeuronAI\Exceptions\SimilarityCalculationException;
use NeuronAI\Exceptions\VectorStoreException;
use NeuronAI\RAG\Document;
use NeuronAI\RAG\VectorStore\Searches\SimilaritySearch;

class MemoryVectorStore implements VectorStoreInterface
{
    /** @var array<Document> */
    private array $documents = [];

    public function __construct(protected int $topK = 4) {}

    public function addDocument(Document $document): void
    {
        $this->documents[] = $document;
    }

    public function addDocuments(array $documents): void
    {
        $this->documents = \array_merge($this->documents, $documents);
    }

    public function similaritySearch(array $embedding): array
    {
        $distances = [];

        foreach ($this->documents as $index => $document) {
            if ($document->embedding === null) {
                throw new VectorStoreException("Document with the following content has no embedding: {$document->content}");
            }
            $dist = $this->cosineSimilarity($embedding, $document->embedding);
            $distances[$index] = $dist;
        }

        \asort($distances); // Sort by distance (ascending).

        $topKIndices = \array_slice(\array_keys($distances), 0, $this->topK, true);

        return \array_reduce($topKIndices, function ($carry, $index) {
            $carry[] = $this->documents[$index];
            return $carry;
        }, []);
    }


    public function cosineSimilarity(array $vector1, array $vector2): float
    {
        return SimilaritySearch::cosine($vector1, $vector2);
    }
}
