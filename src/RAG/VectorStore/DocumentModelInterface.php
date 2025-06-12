<?php

namespace NeuronAI\RAG\VectorStore;

interface DocumentModelInterface extends \JsonSerializable
{
    public function getId(): string|int;

    public function getContent(): string;

    public function getEmbedding(): array;

    public function getSourceType(): string;

    public function getSourceName(): string;

    public function getScore(): float;

    public function getCustomFields(): array;
}
