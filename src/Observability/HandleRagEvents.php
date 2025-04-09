<?php

namespace NeuronAI\Observability;

use NeuronAI\Observability\Events\InstructionsChanged;
use NeuronAI\Observability\Events\InstructionsChanging;
use NeuronAI\Observability\Events\VectorStoreResult;
use NeuronAI\Observability\Events\VectorStoreSearching;

trait HandleRagEvents
{
    public function vectorStoreSearching(\NeuronAI\AgentInterface $agent, string $event, VectorStoreSearching $data)
    {
        if (!$this->inspector->canAddSegments()) {
            return;
        }

        $id = \md5($data->question->getContent());

        $this->segments[$id] = $this->inspector
            ->startSegment(self::SEGMENT_TYPE.'-vector-search', "vectorSearch( {$data->question->getContent()} )")
            ->setColor(self::SEGMENT_COLOR);
    }

    public function vectorStoreResult(\NeuronAI\AgentInterface $agent, string $event, VectorStoreResult $data)
    {
        $id = \md5($data->question->getContent());

        if (\array_key_exists($id, $this->segments)) {
            $this->segments[$id]
                ->addContext('Data', [
                    'question' => $data->question->getContent(),
                    'documents' => \count($data->documents)
                ])
                ->end();
        }
    }

    public function instructionsChanging(\NeuronAI\AgentInterface $agent, string $event, InstructionsChanging $data)
    {
        if (!$this->inspector->canAddSegments()) {
            return;
        }

        $id = \md5($data->instructions);

        $this->segments['instructions-'.$id] = $this->inspector
            ->startSegment(self::SEGMENT_TYPE.'-instructions')
            ->setColor(self::SEGMENT_COLOR);
    }

    public function instructionsChanged(\NeuronAI\AgentInterface $agent, string $event, InstructionsChanged $data)
    {
        $id = 'instructions-'.\md5($data->previous);

        if (\array_key_exists($id, $this->segments)) {
            $this->segments[$id]
                ->addContext('Instructions', [
                    'previous' => $data->previous,
                    'current' => $data->current
                ])
                ->end();
        }
    }
}
