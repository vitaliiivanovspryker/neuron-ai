<?php

namespace NeuronAI\Chat\Messages;

class Message implements \JsonSerializable
{
    const ROLE_USER = 'user';
    const ROLE_ASSISTANT = 'assistant';

    protected ?Usage $usage = null;

    public function __construct(
        protected string $role,
        protected string $content
    ) {}

    public function getRole(): string
    {
        return $this->role;
    }

    public function getContent(): string
    {
        return $this->content;
    }

    public function setUsage(Usage $usage): static
    {
        $this->usage = $usage;
        return $this;
    }

    public function getUsage(): ?Usage
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
