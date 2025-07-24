<?php

declare(strict_types=1);

namespace NeuronAI\Chat\History;

use NeuronAI\Chat\Messages\Message;
use NeuronAI\Exceptions\ChatHistoryException;

interface TrimmerInterface
{
    /**
     * Trim the messages' array to fit within the context window while maintaining conversation integrity
     *
     * @param Message[] $messages Array of messages to trim
     * @param int $contextWindow Maximum token limit
     * @return Message[] Trimmed array of messages
     * @throws ChatHistoryException When messages cannot be trimmed while maintaining integrity
     */
    public function trim(array $messages, int $contextWindow): array;

    /**
     * Validate that messages array follow conversation rules
     *
     * @param Message[] $messages Array of messages to validate
     * @return bool True if valid, false otherwise
     */
    public function validate(array $messages): bool;
}
