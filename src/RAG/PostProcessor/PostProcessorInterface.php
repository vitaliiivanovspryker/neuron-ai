<?php

namespace NeuronAI\RAG\PostProcessor;

use NeuronAI\Chat\Messages\Message;
use NeuronAI\RAG\Document;

interface PostProcessorInterface
{
    /**
     * Process an array of documents and return the processed documents.
     *
     * @param Message $question The question to process the documents for.
     * @param array<Document> $documents The documents to process.
     * @return array<Document> The processed documents.
     */
    public function process(Message $question, array $documents): array;
}
