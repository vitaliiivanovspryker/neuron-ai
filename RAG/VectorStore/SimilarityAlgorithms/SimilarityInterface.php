<?php

namespace App\Extensions\NeuronAI\RAG\VectorStore\SimilarityAlgorithms;

interface SimilarityInterface
{
    public function calculate(array $vector1, array $vector2): float;
}
