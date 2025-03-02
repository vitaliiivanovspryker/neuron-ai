<?php

namespace NeuronAI\RAG\DataReader;

use NeuronAI\RAG\Document;

interface DataReaderInterface
{
    /**
     * @return array<Document>
     */
    public function getDocuments(): array;
}
