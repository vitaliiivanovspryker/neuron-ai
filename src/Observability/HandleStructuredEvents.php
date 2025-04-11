<?php

namespace NeuronAI\Observability;

use NeuronAI\Observability\Events\Deserialized;
use NeuronAI\Observability\Events\Deserializing;
use NeuronAI\Observability\Events\Extracted;
use NeuronAI\Observability\Events\Extracting;
use NeuronAI\Observability\Events\Validated;
use NeuronAI\Observability\Events\Validating;

trait HandleStructuredEvents
{
    protected function extracting(\NeuronAI\AgentInterface $agent, string $event, Extracting $data)
    {
        $id = $this->getMessageId($data->message).'-extract';

        $this->segments[$id] = $this->inspector->startSegment('structured-extract')
            ->setColor(self::SEGMENT_COLOR);
    }

    protected function extracted(\NeuronAI\AgentInterface $agent, string $event, Extracted $data)
    {
        $id = $this->getMessageId($data->message).'-extract';

        if (\array_key_exists($id, $this->segments)) {
            $this->segments[$id]->addContext(
                'Data',
                [
                    'response' => $data->message->jsonSerialize(),
                    'json' => $data->json,
                ]
            )->addContext(
                'Schema', $data->schema
            )->end();
            unset($this->segments[$id]);
        }
    }

    protected function deserializing(\NeuronAI\AgentInterface $agent, string $event, Deserializing $data)
    {

    }

    protected function deserialized(\NeuronAI\AgentInterface $agent, string $event, Deserialized $data)
    {

    }

    protected function validating(\NeuronAI\AgentInterface $agent, string $event, Validating $data)
    {

    }

    protected function validated(\NeuronAI\AgentInterface $agent, string $event, Validated $data)
    {

    }
}
