<?php

namespace NeuronAI;

use NeuronAI\Chat\History\InMemoryChatHistory;
use NeuronAI\Chat\Messages\AssistantMessage;
use NeuronAI\Events\MessageSaved;
use NeuronAI\Events\MessageSaving;
use NeuronAI\Events\MessageSending;
use NeuronAI\Events\MessageSent;
use NeuronAI\Events\ToolCalled;
use NeuronAI\Events\ToolCalling;
use NeuronAI\Exceptions\InvalidMessageInstance;
use NeuronAI\Exceptions\MissingCallbackParameter;
use NeuronAI\Exceptions\ToolCallableNotSet;
use NeuronAI\Observability\AgentMonitoring;
use NeuronAI\Providers\AIProviderInterface;
use NeuronAI\Chat\Messages\Message;
use NeuronAI\Chat\Messages\UserMessage;
use NeuronAI\Tools\Tool;
use NeuronAI\Tools\ToolCallMessage;
use NeuronAI\Tools\ToolInterface;

class Agent implements AgentInterface
{
    use ResolveTools;
    use ResolveChatHistory;

    /**
     * The AI provider instance.
     *
     * @var AIProviderInterface
     */
    protected AIProviderInterface $provider;

    /**
     * The system message.
     *
     * @var ?string
     */
    protected ?string $instructions = null;

    /**
     * @var AgentMonitoring
     */
    private AgentMonitoring $observer;

    public static function make(...$args): static
    {
        return new static(...$args);
    }

    public function setProvider(AIProviderInterface $provider): AgentInterface
    {
        $this->provider = $provider;
        return $this;
    }

    public function provider(): AIProviderInterface
    {
        return $this->provider;
    }

    /**
     * Execute the chat.
     *
     * @param Message|array $messages
     * @return Message
     * @throws MissingCallbackParameter
     * @throws ToolCallableNotSet
     */
    public function chat(Message|array $messages): Message
    {
        $this->notify('chat-start');

        $messages = is_array($messages) ? $messages : [$messages];

        foreach ($messages as $message) {
            $this->notify('message-saving', new MessageSaving($message));
            $this->resolveChatHistory()->addMessage($message);
            $this->notify('message-saved', new MessageSaved($message));
        }

        $message = \end($messages);

        $this->notify(
            'message-sending',
            new MessageSending($message)
        );

        $response = $this->provider()
            ->systemPrompt($this->instructions())
            ->setTools($this->tools())
            ->chat(
                $this->resolveChatHistory()->getMessages()
            );

        $this->notify(
            'message-sent',
            new MessageSent($message, $response)
        );

        if ($response instanceof ToolCallMessage) {
            foreach ($response->getTools() as $tool) {
                $this->notify('tool-calling', new ToolCalling($tool));
                $tool->execute();
                $this->notify('tool-called', new ToolCalled($tool));
            }

            // Resubmit the ToolCallMessage
            $response = $this->chat($response);
        }

        $this->notify('message-saving', new MessageSaving($response));
        $this->resolveChatHistory()->addMessage($response);
        $this->notify('message-saved', new MessageSaved($response));

        $this->notify('chat-stop');
        return $response;
    }

    public function instructions(): ?string
    {
        return $this->instructions;
    }

    public function setInstructions(?string $instructions): AgentInterface
    {
        $this->instructions = $instructions;
        return $this;
    }

    public function observe(\Inspector\Inspector $inspector): AgentInterface
    {
        $this->observer = new AgentMonitoring($inspector);
        return $this;
    }

    protected function notify(string $event = "*", $data = null): void
    {
        $this->observer->update($this, $event, $data);
    }
}
