<?php

namespace NeuronAI\Observability;

use NeuronAI\Observability\Events\WorkflowEnd;
use NeuronAI\Observability\Events\WorkflowNodeEnd;
use NeuronAI\Observability\Events\WorkflowNodeStart;
use NeuronAI\Observability\Events\WorkflowStart;
use NeuronAI\Workflow\Edge;

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
                    'nodes' => \array_keys($data->nodes),
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
                ->addContext('Last Reply', $data->state->all());
        } elseif ($this->inspector->canAddSegments()) {
            $transaction = $this->inspector->transaction();
            $transaction->addContext('Last Reply', $data->state->all());
            $transaction->setResult('success');
        }
    }

    public function workflowNodeStart(\SplSubject $workflow, string $event, WorkflowNodeStart $data)
    {
        if (!$this->inspector->canAddSegments()) {
            return;
        }

        $this->segments[$data->node] = $this->inspector
            ->startSegment('workflow-node', $data->node)
            ->setColor(self::SEGMENT_COLOR);
    }

    public function workflowNodeEnd(\SplSubject $workflow, string $event, WorkflowNodeEnd $data)
    {
        if (\array_key_exists($data->node, $this->segments)) {
            $this->segments[$data->node]->end();
        }
    }
}
