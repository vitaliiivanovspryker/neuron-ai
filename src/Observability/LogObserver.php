<?php declare(strict_types=1);

namespace NeuronAI\Observability;

use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;

class LogObserver implements \SplObserver
{
    private EventSerializer $serializer;

    public function __construct(
        private readonly LoggerInterface $logger
    ) {
        $this->serializer = new EventSerializer();
    }

    public function update(\SplSubject $subject, string $event = null, mixed $data = null): void
    {
        if ($event !== null) {
            $this->logger->log(
                LogLevel::INFO,
                $event,
                $this->serializer->toArray($data)
            );
        }
    }
}
