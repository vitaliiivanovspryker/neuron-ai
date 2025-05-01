<?php

namespace NeuronAI\RAG\VectorStore;

use NeuronAI\Exceptions\NeuronException;
use NeuronAI\Exceptions\SimilarityCalculationException;
use NeuronAI\Exceptions\VectorStoreException;
use NeuronAI\RAG\Document;
use NeuronAI\RAG\VectorStore\Searches\SimilaritySearch;

class FileVectorStore implements VectorStoreInterface
{
    protected array $store = [];

    public function __construct(
        protected string $directory,
        protected int $topK = 4,
        protected string $name = 'neuron',
        protected string $ext = '.store'
    ) {
        if (!\is_dir($this->directory)) {
            throw new VectorStoreException("Directory '{$this->directory}' does not exist");
        }

        $this->init();
    }

    protected function init()
    {
        if (\is_file($this->getFilePath())) {
            $this->store = \json_decode(\file_get_contents($this->getFilePath()), true);
        }
    }

    protected function getFilePath(): string
    {
        return $this->directory . DIRECTORY_SEPARATOR . $this->name.$this->ext;
    }

    protected function updateFile(): void
    {
        \file_put_contents($this->getFilePath(), json_encode($this->store), LOCK_EX);
    }

    public function addDocument(Document $document): void
    {
        $this->addDocuments([$document]);;
    }

    public function addDocuments(array $documents): void
    {
        foreach ($documents as $document) {
            $this->store[] = $document->jsonSerialize();
        };
        $this->updateFile();
    }

    public function similaritySearch(array $embedding): array
    {
        $distances = [];

        foreach ($this->store as $index => $document) {
            if (empty($document['embedding'])) {
                throw new VectorStoreException("Document with the following content has no embedding: {$document['content']}");
            }
            $dist = $this->cosineSimilarity($embedding, $document['embedding']);
            $distances[$index] = $dist;
        }

        \asort($distances); // Sort by distance (ascending).

        $topKIndices = \array_slice(\array_keys($distances), 0, $this->topK, true);

        return \array_reduce($topKIndices, function ($carry, $index) {
            $item = $this->store[$index];
            $document = new Document($item['content']);
            $document->embedding = $item['embedding'];
            $document->sourceType = $item['sourceType'];
            $document->sourceName = $item['sourceName'];
            $document->id = $item['id'];
            $carry[] = $document;
            return $carry;
        }, []);
    }


    protected function cosineSimilarity(array $vector1, array $vector2): float
    {
        return SimilaritySearch::cosine($vector1, $vector2);
    }
}
