<?php

namespace NeuronAI\Observability;

use NeuronAI\AgentInterface;
use NeuronAI\Observability\Events\InstructionsChanged;
use NeuronAI\Observability\Events\InstructionsChanging;
use NeuronAI\Observability\Events\PostProcessed;
use NeuronAI\Observability\Events\PostProcessing;
use NeuronAI\Observability\Events\VectorStoreResult;
use NeuronAI\Observability\Events\VectorStoreSearching;

trait HandleRagEvents
{
    public function vectorStoreSearching(AgentInterface $agent, string $event, VectorStoreSearching $data)
    {
        if (!$this->inspector->canAddSegments()) {
            return;
        }

        $id = \md5($data->question->getContent());

        $this->segments[$id] = $this->inspector
            ->startSegment(self::SEGMENT_TYPE.'-vector-search', "vectorSearch( {$data->question->getContent()} )")
            ->setColor(self::SEGMENT_COLOR);
    }

    public function vectorStoreResult(AgentInterface $agent, string $event, VectorStoreResult $data)
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

    /*public function instructionsChanging(AgentInterface $agent, string $event, InstructionsChanging $data)
    {
        if (!$this->inspector->canAddSegments()) {
            return;
        }

        $id = \md5($data->instructions);

        $this->segments['instructions-'.$id] = $this->inspector
            ->startSegment(self::SEGMENT_TYPE.'-instructions', 'withInstructions()')
            ->setColor(self::SEGMENT_COLOR);
    }

    public function instructionsChanged(AgentInterface $agent, string $event, InstructionsChanged $data)
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
    }*/

    public function postProcessing(AgentInterface $agent, string $event, PostProcessing $data)
    {
        if (!$this->inspector->canAddSegments()) {
            return;
        }

        $this->segments[$data->processor] = $this->inspector
            ->startSegment(self::SEGMENT_TYPE.'-postprocessing', $data->processor)
            ->setColor(self::SEGMENT_COLOR)
            ->addContext('Question', $data->question->jsonSerialize())
            ->addContext('Documents', $data->documents);
    }

    public function postProcessed(AgentInterface $agent, string $event, PostProcessed $data)
    {
        if (\array_key_exists($data->processor, $this->segments)) {
            $this->segments[$data->processor]->addContext('PostProcess', $data->documents)
                ->end();
        }
    }
}
