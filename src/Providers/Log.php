<?php

namespace NeuronAI\Providers;

use NeuronAI\Chat\Messages\AssistantMessage;
use NeuronAI\Chat\Messages\Message;
use Psr\Log\LoggerInterface;

class Log implements AIProviderInterface
{
    /**
     * Log AI driver constructor.
     *
     * @param LoggerInterface|null $logger
     */
    public function __construct(protected ?LoggerInterface $logger = null)
    {
    }

    /**
     * @inerhitDoc
     */
    public function systemPrompt(string $prompt): AIProviderInterface
    {
        return $this;
    }

    /**
     * @param array|string $prompt
     * @return Message
     * @throws \Exception
     */
    public function chat(array|string $prompt): Message
    {
        if ($this->logger) {
            if (is_string($prompt)) {
                $this->logger->debug("Prompting AI with: {$prompt}");
            } else {
                $this->logger->debug('Prompting AI with: ', $prompt);
            }
        }

        return new AssistantMessage("I'm the fake Neuron AI driver");
    }

    public function setTools(array $tools): AIProviderInterface
    {
        return $this;
    }
}
