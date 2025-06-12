<?php

namespace NeuronAI\RAG\VectorStore;

interface VectorStoreInterface
{
    public function addDocument(DocumentModelInterface $document): void;

    /**
     * @param  DocumentModelInterface[]  $documents
     */
    public function addDocuments(array $documents): void;

    /**
     * Return docs most similar to the embedding.
     *
     * @param  float[]  $embedding
     * @return DocumentModelInterface[]
     */
    public function similaritySearch(array $embedding, string $documentModel): iterable;
}
