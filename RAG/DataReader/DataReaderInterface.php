<?php

namespace App\Extensions\NeuronAI\RAG\DataReader;

use App\Extensions\NeuronAI\RAG\Document;

interface DataReaderInterface
{
    /**
     * @return array<Document>
     */
    public function getDocuments(): array;
}
