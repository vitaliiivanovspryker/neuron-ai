<?php

namespace NeuronAI\Messages;

abstract class AbstractMessage implements \JsonSerializable
{
    protected Usage $usage;

    abstract public function getRole(): string;

    abstract public function getContent(): string;

    public function setUsage(Usage $usage): static
    {
        $this->usage = $usage;
        return $this;
    }

    public function getUsage(): Usage
    {
        return $this->usage;
    }

    public function toArray(): array
    {
        return [
            'role' => $this->getRole(),
            'content' => $this->getContent(),
        ];
    }

    public function jsonSerialize(): array
    {
        return $this->toArray();
    }
}
