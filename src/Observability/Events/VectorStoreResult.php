<?php

namespace NeuronAI\Observability\Events;

use NeuronAI\Chat\Messages\Message;
use NeuronAI\RAG\VectorStore\DocumentModelInterface;

class VectorStoreResult
{
    /**
     * @param DocumentModelInterface[] $documents
     */
    public function __construct(
        public Message $question,
        public array $documents,
    ) {
    }
}
