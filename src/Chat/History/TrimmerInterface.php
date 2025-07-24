<?php

declare(strict_types=1);

namespace NeuronAI\Chat\History;

use NeuronAI\Chat\Messages\Message;

interface TrimmerInterface
{
    public function __construct(TokenCounterInterface $tokenCounter);

    /**
     * Trim the messages' array to fit within the context window while maintaining conversation integrity.
     *
     * @param Message[] $messages Array of messages to trim
     * @return Message[] Trimmed array of messages
     */
    public function trim(array $messages, int $contextWindow): array;

    /**
     * Validate that messages array follow conversation rules.
     *
     * @param Message[] $messages Array of messages to validate
     */
    public function validate(array $messages): bool;
}
