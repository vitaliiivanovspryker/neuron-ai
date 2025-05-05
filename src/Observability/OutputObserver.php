<?php declare(strict_types=1);

namespace NeuronAI\Observability;

class OutputObserver implements \SplObserver
{
    private EventSerializer $serializer;

    public function __construct()
    {
        $this->serializer = new EventSerializer();
    }

    public function update(\SplSubject $subject, string $event = null, mixed $data = null): void
    {
        echo sprintf("%s - %s\n\n", $event, json_encode($this->serializer->toArray($data)));
    }
}
