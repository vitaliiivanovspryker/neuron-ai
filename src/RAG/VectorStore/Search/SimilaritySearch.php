<?php

namespace NeuronAI\RAG\VectorStore\Search;

use NeuronAI\Exceptions\VectorStoreException;

class SimilaritySearch
{
    public static function cosine(array $vec1, array $vec2): float|int
    {
        if (\count($vec1) !== \count($vec2)) {
            throw new VectorStoreException('Arrays must have the same length to apply cosine similarity.');
        }

        $dotProduct = 0.0;
        $mag1 = 0.0;
        $mag2 = 0.0;

        foreach ($vec1 as $key => $value) {
            if (isset($vec2[$key])) {
                $dotProduct += $value * $vec2[$key];
            }
            $mag1 += $value ** 2;
        }

        foreach ($vec2 as $value) {
            $mag2 += $value ** 2;
        }

        if ($mag1 === 0.0 || $mag2 === 0.0) {
            return 0.0;
        }

        return 1 - $dotProduct / (sqrt($mag1) * sqrt($mag2));
    }
}
