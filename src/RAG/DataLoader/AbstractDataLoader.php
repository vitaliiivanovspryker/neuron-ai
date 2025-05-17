<?php

namespace NeuronAI\RAG\DataLoader;

abstract class AbstractDataLoader implements DataLoaderInterface
{
    protected int $maxLength = 1000;
    protected string $separator = '.';
    protected int $wordOverlap = 0;

    public static function for(...$args): static
    {
        /** @phpstan-ignore new.static */
        return new static(...$args);
    }

    public function withMaxLength(int $maxLength): DataLoaderInterface
    {
        $this->maxLength = $maxLength;
        return $this;
    }

    public function withSeparator(string $separator): DataLoaderInterface
    {
        $this->separator = $separator;
        return $this;
    }

    public function withOverlap(int $overlap): DataLoaderInterface
    {
        $this->wordOverlap = $overlap;
        return $this;
    }
}
