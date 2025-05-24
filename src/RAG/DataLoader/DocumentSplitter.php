<?php

namespace NeuronAI\RAG\DataLoader;

use NeuronAI\RAG\Document;

class DocumentSplitter
{
    /**
     * @return array<Document>
     */
    public static function splitDocument(Document $document, int $maxLength = 1000, string $separator = ' ', int $wordOverlap = 0): array
    {
        $text = $document->content;

        if (empty($text)) {
            return [];
        }

        if (\strlen($text) <= $maxLength) {
            if (empty($document->hash)) {
                $document->hash = \hash('sha256', $text);
            }
            return [$document];
        }

        $parts = \explode($separator, $text);

        $chunks = self::createChunksWithOverlap($parts, $maxLength, $separator, $wordOverlap);

        $split = [];
        $chunkNumber = 0;
        foreach ($chunks as $chunk) {
            $newDocument = new Document($chunk);
            $newDocument->hash = \hash('sha256', $chunk);
            $newDocument->sourceType = $document->sourceType;
            $newDocument->sourceName = $document->sourceName;
            $newDocument->chunkNumber = $chunkNumber;
            $chunkNumber++;
            $split[] = $newDocument;
        }

        return $split;
    }

    /**
     * @param  array<Document>  $documents
     * @return array<Document>
     */
    public static function splitDocuments(array $documents, int $maxLength = 1000, string $separator = '.', int $wordOverlap = 0): array
    {
        $split = [];

        foreach ($documents as $document) {
            $split = \array_merge($split, static::splitDocument($document, $maxLength, $separator, $wordOverlap));
        }

        return $split;
    }

    /**
     * @param  array<string>  $words
     * @return array<string>
     */
    private static function createChunksWithOverlap(array $words, int $maxLength, string $separator, int $wordOverlap): array
    {
        $chunks = [];
        $currentChunk = [];
        $currentChunkLength = 0;
        foreach ($words as $word) {
            if ($word === '') {
                continue;
            }

            if ($currentChunkLength + \strlen($separator.$word) <= $maxLength || $currentChunk === []) {
                $currentChunk[] = $word;
                $currentChunkLength = self::calculateChunkLength($currentChunk, $separator);
            } else {
                // Add the chunk with overlap
                $chunks[] = \implode($separator, $currentChunk);

                // Calculate overlap words
                $calculatedOverlap = \min($wordOverlap, \count($currentChunk) - 1);
                $overlapWords = $calculatedOverlap > 0 ? \array_slice($currentChunk, -$calculatedOverlap) : [];

                // Start a new chunk with overlap words
                $currentChunk = [...$overlapWords, $word];
                $currentChunk[0] = \trim($currentChunk[0]);
                $currentChunkLength = self::calculateChunkLength($currentChunk, $separator);
            }
        }

        if ($currentChunk !== []) {
            $chunks[] = \implode($separator, $currentChunk);
        }

        return $chunks;
    }

    /**
     * @param  array<string>  $currentChunk
     */
    private static function calculateChunkLength(array $currentChunk, string $separator): int
    {
        return \array_sum(\array_map('strlen', $currentChunk)) + \count($currentChunk) * \strlen($separator) - 1;
    }
}
