<?php

namespace NeuronAI\RAG\VectorStore;

use NeuronAI\RAG\Document;
use NeuronAI\RAG\VectorStore\SimilarityAlgorithms\CosineSimilarity;
use NeuronAI\RAG\VectorStore\SimilarityAlgorithms\SimilarityInterface;

class MemoryVectorStore implements VectorStoreInterface
{
    /** @var array<Document> */
    private array $documents = [];

    public function __construct(
        protected SimilarityInterface $similarity = new CosineSimilarity()
    ) {}

    public function addDocument(Document $document): void
    {
        $this->documents[] = $document;
    }

    public function addDocuments(array $documents): void
    {
        $this->documents = \array_merge($this->documents, $documents);
    }

    public function similaritySearch(array $embedding, int $k = 4): array
    {
        $distances = [];

        foreach ($this->documents as $index => $document) {
            if ($document->embedding === null) {
                throw new \Exception("Document with the following content has no embedding: {$document->content}");
            }
            $dist = $this->similarity->calculate($embedding, $document->embedding);
            $distances[$index] = $dist;
        }

        \asort($distances); // Sort by distance (ascending).

        $topKIndices = \array_slice(\array_keys($distances), 0, $k, true);

        return \array_reduce($topKIndices, function ($carry, $index) {
            $carry[] = $this->documents[$index];
            return $carry;
        }, []);
    }
}
