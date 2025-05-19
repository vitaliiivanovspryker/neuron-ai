<?php

namespace NeuronAI\Observability;

use NeuronAI\Observability\Events\ToolCalled;
use NeuronAI\Observability\Events\ToolCalling;

trait HandleToolEvents
{
    public function toolCalling(\NeuronAI\AgentInterface $agent, string $event, ToolCalling $data)
    {
        if (!$this->inspector->canAddSegments()) {
            return;
        }

        $this->segments[$data->tool->getName()] = $this->inspector
            ->startSegment(self::SEGMENT_TYPE.'-tool-call', "toolCall({$data->tool->getName()})")
            ->setColor(self::SEGMENT_COLOR);
    }

    public function toolCalled(\NeuronAI\AgentInterface $agent, string $event, ToolCalled $data)
    {
        if (\array_key_exists($data->tool->getName(), $this->segments)) {
            $this->segments[$data->tool->getName()]
                ->addContext('Tool', $data->tool->jsonSerialize())
                ->end();
        }
    }
}
