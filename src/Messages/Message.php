<?php

namespace NeuronAI\Messages;

class Message extends AbstractMessage
{
    const ROLE_USER = 'user';
    const ROLE_ASSISTANT = 'assistant';

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
}
