<?php

namespace NeuronAI\RAG\DataLoader;

use NeuronAI\RAG\VectorStore\DocumentModelInterface;

interface DataLoaderInterface
{
    /**
     * @return DocumentModelInterface[]
     */
    public function getDocuments(): array;
}
