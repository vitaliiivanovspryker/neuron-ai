<?php

namespace NeuronAI;

use NeuronAI\Chat\Messages\Message;
use NeuronAI\Chat\Messages\ToolCallMessage;
use NeuronAI\Observability\Events\MessageSaved;
use NeuronAI\Observability\Events\MessageSaving;
use NeuronAI\Observability\Events\InferenceStart;
use NeuronAI\Observability\Events\InferenceStop;
use NeuronAI\Exceptions\MissingCallbackParameter;
use NeuronAI\Exceptions\ToolCallableNotSet;

trait HandleChat
{
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
            'inference-start',
            new InferenceStart($message)
        );

        $response = $this->provider()
            ->systemPrompt($this->instructions())
            ->setTools($this->tools())
            ->chat(
                $this->resolveChatHistory()->getMessages()
            );

        $this->notify(
            'inference-stop',
            new InferenceStop($message, $response)
        );

        if ($response instanceof ToolCallMessage) {
            $toolCallResult = $this->executeTools($response);
            $response = $this->chat([$response, $toolCallResult]);
        }

        $this->notify('message-saving', new MessageSaving($response));
        $this->resolveChatHistory()->addMessage($response);
        $this->notify('message-saved', new MessageSaved($response));

        $this->notify('chat-stop');
        return $response;
    }
}
