<?php

declare(strict_types=1);

namespace NeuronAI\Tests\Workflow;

use NeuronAI\Chat\Enums\MessageRole;
use NeuronAI\Chat\Messages\Message;
use NeuronAI\Observability\Observable;
use NeuronAI\Workflow\NodeInterface;

class TestNode implements NodeInterface
{
    use Observable;

    public function __construct(
        private readonly string $data = ''
    ) {
    }

    public function execute(Message|array $messages): Message
    {
        $this->notify('test', "Evaluate {$this->data}");

        return new Message(MessageRole::ASSISTANT, $this->data);
    }
}
