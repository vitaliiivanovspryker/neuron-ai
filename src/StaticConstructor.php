<?php

namespace NeuronAI;

trait StaticConstructor
{
    /**
     * Static constructor.
     *
     * @param ...$args
     * @return static
     */
    public static function make(...$args): static
    {
        /** @phpstan-ignore new.static */
        return new static(...$args);
    }
}
