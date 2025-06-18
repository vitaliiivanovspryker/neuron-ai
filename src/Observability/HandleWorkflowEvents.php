<?php

namespace NeuronAI\Observability;

use NeuronAI\Observability\Events\WorkflowEnd;
use NeuronAI\Observability\Events\WorkflowStart;
use NeuronAI\Workflow\Edge;
use NeuronAI\Workflow\NodeInterface;

trait HandleWorkflowEvents
{
    public function workflowStart(\SplSubject $workflow, string $event, WorkflowStart $data)
    {
        if (!$this->inspector->isRecording()) {
            return;
        }

        if ($this->inspector->needTransaction()) {
            $this->inspector->startTransaction($workflow::class)
                ->setType('neuron-workflow')
                ->addContext('List', [
                    'nodes' => \array_map(fn (NodeInterface $node) => $node::class, $data->nodes),
                    'edges' => \array_map(function (Edge $edge) {
                        return [
                            'from' => $edge->getFrom(),
                            'to' => $edge->getTo(),
                            'has_condition' => $edge->hasCondition(),
                        ];
                    }, $data->edges)
                ]);
        } elseif ($this->inspector->canAddSegments()) {
            $this->segments[$workflow::class] = $this->inspector->startSegment('neuron-workflow', $workflow::class)
                ->setColor(self::SEGMENT_COLOR);
        }
    }

    public function workflowEnd(\SplSubject $workflow, string $event, WorkflowEnd $data)
    {
        if (\array_key_exists($workflow::class, $this->segments)) {
            $this->segments[$workflow::class]
                ->end()
                ->addContext('Last Reply', $data->lastReply->jsonSerialize());
        } elseif ($this->inspector->canAddSegments()) {
            $transaction = $this->inspector->transaction();
            $transaction->addContext('Last Reply', $data->lastReply->jsonSerialize());
            $transaction->setResult('success');
        }
    }
}
