<?php

namespace NeuronAI;

use NeuronAI\Chat\History\InMemoryChatHistory;
use NeuronAI\Events\MessageSaved;
use NeuronAI\Events\MessageSaving;
use NeuronAI\Events\MessageSending;
use NeuronAI\Events\MessageSent;
use NeuronAI\Events\ToolCalled;
use NeuronAI\Events\ToolCalling;
use NeuronAI\Exceptions\InvalidMessageInstance;
use NeuronAI\Exceptions\MissingCallbackParameter;
use NeuronAI\Exceptions\ToolCallableNotSet;
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
     * @var array<\SplObserver>
     */
    private array $observers = [];

    public function __construct()
    {
        // A special event group for observers that want to listen to all events.
        $this->observers["*"] = [];
    }

    public static function make(...$args): static
    {
        return new static(...$args);
    }

    public function setProvider(AIProviderInterface $provider): self
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
     * @param Message $message
     * @return Message
     * @throws MissingCallbackParameter
     * @throws ToolCallableNotSet
     */
    public function chat(Message $message): Message
    {
        $this->notify('chat-start');

        $this->notify('message-saving', new MessageSaving($message));
        $this->resolveChatHistory()->addMessage($message);
        $this->notify('message-saved', new MessageSaved($message));

        $this->notify(
            'message-sending',
            new MessageSending($message)
        );

        $response = $this->provider()
            ->systemPrompt($this->instructions())
            ->setTools($this->tools())
            ->chat(
                $this->resolveChatHistory()->jsonSerialize()
            );

        $this->notify('message-saving', new MessageSaving($response));
        $this->resolveChatHistory()->addMessage($response);
        $this->notify('message-saved', new MessageSaved($response));

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
            $this->chat($response);
        }

        $this->notify('chat-stop');
        return $response;
    }

    public function instructions(): ?string
    {
        return $this->instructions;
    }

    public function setInstructions(?string $instructions): self
    {
        $this->instructions = $instructions;
        return $this;
    }

    private function initEventGroup(string $event = "*"): void
    {
        if (!isset($this->observers[$event])) {
            $this->observers[$event] = [];
        }
    }

    private function getEventObservers(string $event = "*"): array
    {
        $this->initEventGroup($event);
        $group = $this->observers[$event];
        $all = $this->observers["*"];

        return \array_merge($group, $all);
    }

    public function observe(\SplObserver $observer, string $event = "*"): self
    {
        $this->attach($observer, $event);
        return $this;
    }

    public function attach(\SplObserver $observer, string $event = "*"): void
    {
        $this->initEventGroup($event);
        $this->observers[$event][] = $observer;
    }

    public function detach(\SplObserver $observer, string $event = "*"): void
    {
        foreach ($this->getEventObservers($event) as $key => $s) {
            if ($s === $observer) {
                unset($this->observers[$event][$key]);
            }
        }
    }

    public function notify(string $event = "*", $data = null): void
    {
        // Broadcasting the '$event' event";
        foreach ($this->getEventObservers($event) as $observer) {
            $observer->update($this, $event, $data);
        }
    }
}
