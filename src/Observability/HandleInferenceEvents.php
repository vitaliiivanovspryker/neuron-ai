<?php

namespace NeuronAI\Observability;

use NeuronAI\AgentInterface;
use NeuronAI\Chat\Messages\ToolCallMessage;
use NeuronAI\Chat\Messages\ToolCallResultMessage;
use NeuronAI\Observability\Events\InferenceStart;
use NeuronAI\Observability\Events\InferenceStop;
use NeuronAI\Observability\Events\MessageSaved;
use NeuronAI\Observability\Events\MessageSaving;

trait HandleInferenceEvents
{
    public function messageSaving(AgentInterface $agent, string $event, MessageSaving $data)
    {
        if (!$this->inspector->canAddSegments()) {
            return;
        }

        $label = $this->getBaseClassName($data->message::class);

        $this->segments[$this->getMessageId($data->message).'-save'] = $this->inspector
            ->startSegment(self::SEGMENT_TYPE.'-chathistory', "save( {$label} )")
            ->setColor(self::SEGMENT_COLOR);
    }

    public function messageSaved(AgentInterface $agent, string $event, MessageSaved $data)
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

    public function inferenceStart(AgentInterface $agent, string $event, InferenceStart $data)
    {
        if (!$this->inspector->canAddSegments()) {
            return;
        }

        $label = $this->getBaseClassName($data->message::class);

        $this->segments[$this->getMessageId($data->message).'-inference'] = $this->inspector
            ->startSegment(self::SEGMENT_TYPE.'-inference', "inference( {$label} )")
            ->setColor(self::SEGMENT_COLOR);
    }

    public function inferenceStop(AgentInterface $agent, string $event, InferenceStop $data)
    {
        $id = $this->getMessageId($data->message).'-inference';

        if (\array_key_exists($id, $this->segments)) {
            $segment = $this->segments[$id]
                ->addContext('Message', $data->message)
                ->addContext('Response', $data->response);
            foreach ($this->getContext($agent) as $key => $value) {
                $segment->addContext($key, $value);
            }
            $segment->end();
        }
    }
}
