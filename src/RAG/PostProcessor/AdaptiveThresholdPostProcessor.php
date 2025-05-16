<?php

namespace NeuronAI\RAG\PostProcessor;

use NeuronAI\Chat\Messages\Message;
use NeuronAI\RAG\Document;

class AdaptiveThresholdPostProcessor implements PostProcessorInterface
{
    private float $multiplier;

    /**
     * Creates a post processor that filters documents using an adaptive threshold
     * based on median and MAD (Median Absolute Deviation).
     *
     * Recommended multiplier values:
     * - 0.2-0.4: High precision mode. For more targeted results with fewer but more relevant documents.
     * - 0.5-0.7: Balanced mode. Recommended setting for general use cases.
     * - 0.8-1.0: High recall mode. For more inclusive results that prioritize coverage.
     * - >1.0: Not recommended as it tends to include almost all documents.
     *
     * @param float $multiplier multiplier for MAD (higher values = more inclusive)
     */
    public function __construct(float $multiplier = 0.6)
    {
        $this->multiplier = $multiplier;
    }

    /**
     * Filters documents using a threshold calculated dynamically with median and MAD
     * for greater robustness against outliers.
     *
     * @param Message $question
     * @param Document[] $documents
     * @return Document[]
     */
    public function process(Message $question, array $documents): array
    {
        if (count($documents) < 2) {
            return $documents;
        }

        $scores = array_map(fn($d) => $d->score, $documents);
        $median = $this->calculateMedian($scores);
        $mad = $this->calculateMAD($scores, $median);

        // If MAD is zero (many equal values), don't filter
        if ($mad <= 0.0001) {
            return $documents;
        }

        // Threshold: median - multiplier * MAD
        $threshold = $median - ($this->multiplier * $mad);

        // Ensure threshold is not negative
        $threshold = max(0, $threshold);

        return \array_values(\array_filter($documents, function ($document) use ($threshold) {
            return $document->score >= $threshold;
        }));
    }

    /**
     * Calculates the median of an array of values
     * 
     * @param float[] $values
     * @return float
     */
    protected function calculateMedian(array $values): float
    {
        sort($values);
        $n = count($values);
        $mid = (int) floor(($n - 1) / 2);

        if ($n % 2) {
            return $values[$mid];
        }

        return ($values[$mid] + $values[$mid + 1]) / 2.0;
    }

    /**
     * Calculates the Median Absolute Deviation (MAD)
     * 
     * @param float[] $values
     * @param float $median The median of the values
     * @return float
     */
    protected function calculateMAD(array $values, float $median): float
    {
        $deviations = array_map(fn($v) => abs($v - $median), $values);

        // MAD is the median of deviations
        return $this->calculateMedian($deviations);
    }
}
