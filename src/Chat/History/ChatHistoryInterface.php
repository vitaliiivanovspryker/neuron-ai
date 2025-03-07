<?php

namespace NeuronAI\Chat\History;

use NeuronAI\Chat\Messages\Message;
use NeuronAI\Chat\Messages\MessageMapperInterface;

interface ChatHistoryInterface extends \JsonSerializable
{
    public function addMessage(Message $message): ChatHistoryInterface;

    public function getMessages(): array;

    public function clear(): ChatHistoryInterface;

    public function calculateTotalUsage(): int;
}
