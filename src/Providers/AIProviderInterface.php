<?php

namespace NeuronAI\Providers;

use NeuronAI\Chat\Messages\Message;
use NeuronAI\Tools\ToolInterface;

interface AIProviderInterface
{
    /**
     * Send predefined instruction to the LLM.
     *
     * @param ?string $prompt
     * @return AIProviderInterface
     */
    public function systemPrompt(?string $prompt): AIProviderInterface;

    /**
     * Set the tools to be exposed to the LLM.
     *
     * @param array<ToolInterface> $tools
     * @return AIProviderInterface
     */
    public function setTools(array $tools): AIProviderInterface;

    /**
     * Send a prompt to the AI agent.
     *
     * @param array<Message> $messages
     * @return Message
     */
    public function chat(array $messages): Message;

    //public function structured(array|string $messages): Message;

    public function stream(array|string $messages): \Generator;

    /**
     * The context window limitation of the LLM.
     *
     * @return ?int
     */
    //public function contextWindow(): ?int;

    /**
     * The maximum number of tokens to generate before stopping.
     *
     * @return ?int
     */
    //public function maxCompletionTokens(): ?int;
}
