<?php

namespace NeuronAI\Chat\History;

use NeuronAI\Chat\Messages\Message;

interface ChatHistoryInterface extends \JsonSerializable
{
    public function addMessage(Message $message): self;

    public function getMessages(): array;

    public function clear(): self;

    public function calculateTotalUsage(): int;
}
