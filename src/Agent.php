<?php

namespace NeuronAI;

use NeuronAI\Chat\InMemoryChatHistory;
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
    /**
     * The AI provider instance.
     *
     * @var AIProviderInterface
     */
    protected AIProviderInterface $provider;

    /**
     * @var AbstractChatHistory
     */
    protected AbstractChatHistory $chatHistory;

    /**
     * The system message.
     *
     * @var ?string
     */
    protected ?string $instructions = null;

    /**
     * Registered tools.
     *
     * @var array<Tool>
     */
    protected array $tools = [];

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
        $this->chatHistory()->addMessage($message);
        $this->notify('message-saved', new MessageSaved($message));

        $this->notify(
            'message-sending',
            new MessageSending($message)
        );

        $response = $this->provider()
            ->systemPrompt($this->instructions())
            ->setTools($this->tools())
            ->chat(
                $this->chatHistory()->toArray()
            );

        $this->notify('message-saving', new MessageSaving($response));
        $this->chatHistory()->addMessage($response);
        $this->notify('message-saved', new MessageSaved($response));

        $this->notify(
            'message-sent',
            new MessageSent($message, $response)
        );

        if ($response instanceof ToolCallMessage) {
            foreach ($response->getTools() as $tool) {
                $this->notify('tool-calling', new ToolCalling($response));
                $tool->execute();
                $this->notify('tool-called', new ToolCalled($response, $tool->getResult()));
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

    /**
     * Get the list of tools.
     *
     * @return array<Tool>
     */
    public function tools(): array
    {
        return $this->tools;
    }

    /**
     * Add a tool.
     *
     * @param ToolInterface $tool
     * @return $this
     */
    public function addTool(ToolInterface $tool): self
    {
        $this->tools[] = $tool;
        return $this;
    }

    public function withChatHistory(AbstractChatHistory $chatHistory): self
    {
        $this->chatHistory = $chatHistory;
        return $this;
    }

    public function chatHistory(): AbstractChatHistory
    {
        if (!isset($this->chatHistory)) {
            $this->chatHistory = new InMemoryChatHistory();
        }

        return $this->chatHistory;
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
