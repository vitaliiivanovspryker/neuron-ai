<?php

namespace NeuronAI\Observability;

use NeuronAI\Chat\Messages\ToolCallMessage;
use NeuronAI\Chat\Messages\ToolCallResultMessage;
use NeuronAI\Observability\Events\InferenceStart;
use NeuronAI\Observability\Events\InferenceStop;
use NeuronAI\Observability\Events\MessageSaved;
use NeuronAI\Observability\Events\MessageSaving;

trait HandleInferenceEvents
{
    public function messageSaving(\NeuronAI\AgentInterface $agent, string $event, MessageSaving $data)
    {
        if (!$this->inspector->canAddSegments()) {
            return;
        }

        if ($data->message instanceof ToolCallMessage || $data->message instanceof ToolCallResultMessage) {
            $label = substr(strrchr(get_class($data->message), '\\'), 1);
        } else {
            $label = $data->message->getContent();
        }

        $this->segments[$this->getMessageId($data->message).'-save'] = $this->inspector
            ->startSegment(self::SEGMENT_TYPE.'-chathistory', "save( {$label} )")
            ->setColor(self::SEGMENT_COLOR);
    }

    public function messageSaved(\NeuronAI\AgentInterface $agent, string $event, MessageSaved $data)
    {
        $id = $this->getMessageId($data->message).'-save';

        if (!\array_key_exists($id, $this->segments)) {
            return;
        }

        $this->segments[$id]
            ->addContext('Message', \array_merge($data->message->jsonSerialize(), $data->message->getUsage() ? [
                'usage' => [
                    'input_tokens' => $data->message->getUsage()->inputTokens,
                    'output_tokens' => $data->message->getUsage()->outputTokens,
                ]
            ] : []))
            ->end();
    }

    public function inferenceStart(\NeuronAI\AgentInterface $agent, string $event, InferenceStart $data)
    {
        if (!$this->inspector->canAddSegments()) {
            return;
        }

        if ($data->message instanceof ToolCallResultMessage) {
            $label = substr(strrchr(get_class($data->message), '\\'), 1);
        } else {
            $label = json_encode($data->message->getContent());
        }

        $this->segments[$this->getMessageId($data->message).'-inference'] = $this->inspector
            ->startSegment(self::SEGMENT_TYPE.'-inference', "inference( {$label} )")
            ->setColor(self::SEGMENT_COLOR);
    }

    public function inferenceStop(\NeuronAI\AgentInterface $agent, string $event, InferenceStop $data)
    {
        $id = $this->getMessageId($data->message).'-inference';

        if (\array_key_exists($id, $this->segments)) {
            $this->segments[$id]
                ->setContext($this->getContext($agent))
                ->addContext('Message', $data->message)
                ->addContext('Response', $data->response)
                ->end();
        }
    }
}
