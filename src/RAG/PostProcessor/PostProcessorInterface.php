<?php

namespace NeuronAI\RAG\PostProcessor;

use NeuronAI\RAG\Document;

interface PostProcessorInterface
{
    /**
     * Process an array of documents and return the processed documents.
     *
     * @param array<Document> $documents
     * @return array<Document>
     */
    public function postProcess(string $question, array $documents): array;
}
