<?php

namespace App\Extensions\NeuronAI\Providers;

use App\Extensions\NeuronAI\Providers\Embeddings\EmbeddingsProviderInterface;

abstract class AbstractAIProvider implements AIProviderInterface
{
    protected EmbeddingsProviderInterface $embeddingsProvider;

    public function setEmbeddingProvider(EmbeddingsProviderInterface $provider): self
    {
        $this->embeddingsProvider = $provider;
        return $this;
    }

    public function embeddings(string $text): array
    {
        return $this->embeddingsProvider->embedText($text);
    }
}
