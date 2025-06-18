<?php

namespace NeuronAI\Workflow;

use NeuronAI\Exceptions\WorkflowException;
use NeuronAI\Observability\Events\AgentError;
use NeuronAI\Observability\Events\WorkflowEnd;
use NeuronAI\Observability\Events\WorkflowNodeEnd;
use NeuronAI\Observability\Events\WorkflowNodeStart;
use NeuronAI\Observability\Events\WorkflowStart;
use NeuronAI\Observability\Observable;
use NeuronAI\Workflow\Exporter\ExporterInterface;
use NeuronAI\Workflow\Exporter\MermaidExporter;
use NeuronAI\Workflow\Persistence\InMemoryPersistence;
use NeuronAI\Workflow\Persistence\PersistenceInterface;
use SplSubject;

class Workflow implements SplSubject
{
    use Observable;

    /**
     * @var NodeInterface[]
     */
    protected array $nodes = [];

    /**
     * @var Edge[]
     */
    protected array $edges = [];

    protected ?string $startNode = null;

    protected ?string $endNode = null;

    protected ExporterInterface $exporter;

    protected PersistenceInterface $persistence;

    protected string $workflowId;

    public function __construct(?PersistenceInterface $persistence = null, ?string $workflowId = null)
    {
        $this->exporter = new MermaidExporter();
        $this->persistence = $persistence ?? new InMemoryPersistence();
        $this->workflowId = $workflowId ?? \uniqid('neuron_workflow_');
    }

    public function validate(): void
    {
        if ($this->startNode === null) {
            throw new WorkflowException('Start node must be defined');
        }

        if ($this->endNode === null) {
            throw new WorkflowException('End node must be defined');
        }

        if (!isset($this->getNodes()[$this->startNode])) {
            throw new WorkflowException("Start node {$this->startNode} does not exist");
        }

        if (!isset($this->getNodes()[$this->endNode])) {
            throw new WorkflowException("End node {$this->endNode} does not exist");
        }

        foreach ($this->getEdges() as $edge) {
            if (!isset($this->getNodes()[$edge->getFrom()])) {
                throw new WorkflowException("Edge from node {$edge->getFrom()} does not exist");
            }

            if (!isset($this->getNodes()[$edge->getTo()])) {
                throw new WorkflowException("Edge to node {$edge->getTo()} does not exist");
            }
        }
    }

    /**
     * @throws WorkflowInterrupt|WorkflowException
     */
    protected function execute(
        string $currentNode,
        WorkflowState $state,
        bool $resuming = false,
        array|string|int $humanFeedback = []
    ): WorkflowState {
        $context = new WorkflowContext(
            $this->workflowId,
            $currentNode,
            $this->persistence,
            $state
        );

        if ($resuming) {
            $context->setResuming(true, [$currentNode => $humanFeedback]);
        }

        try {
            while ($currentNode !== $this->endNode) {
                $node = $this->nodes[$currentNode];
                $node->setContext($context);

                $this->notify('workflow-node-start', new WorkflowNodeStart($currentNode, $state));
                try {
                    $state = $node->run($state);
                } catch (WorkflowInterrupt $interrupt) {
                    throw $interrupt;
                } catch (\Throwable $exception) {
                    $this->notify('error', new AgentError($exception));
                    throw $exception;
                }
                $this->notify('workflow-node-stop', new WorkflowNodeEnd($currentNode, $state));

                $nextNode = $this->findNextNode($currentNode, $state);

                if ($nextNode === null) {
                    throw new WorkflowException("No valid edge found from node {$currentNode}");
                }

                $currentNode = $nextNode;

                // Update the context before the next iteration or end node
                $context = new WorkflowContext(
                    $this->workflowId,
                    $currentNode,
                    $this->persistence,
                    $state
                );
            }

            $endNode = $this->nodes[$this->endNode];
            $endNode->setContext($context);
            return $endNode->run($state);

        } catch (WorkflowInterrupt $interrupt) {
            $this->persistence->save($this->workflowId, $interrupt);
            $this->notify('workflow-interrupt', $interrupt);
            throw $interrupt;
        }
    }

    /**
     * @throws WorkflowInterrupt|WorkflowException
     */
    public function run(?WorkflowState $initialState = null): WorkflowState
    {
        $this->notify('workflow-start', new WorkflowStart($this->getNodes(), $this->getEdges()));
        try {
            $this->validate();
        } catch (WorkflowException $exception) {
            $this->notify('error', new AgentError($exception));
            throw $exception;
        }

        $state = $initialState ?? new WorkflowState();
        $currentNode = $this->startNode;

        $result = $this->execute($currentNode, $state);
        $this->notify('workflow-end', new WorkflowEnd($result));

        return $result;
    }

    /**
     * @throws WorkflowInterrupt|WorkflowException
     */
    public function resume(array|string|int $humanFeedback): WorkflowState
    {
        $this->notify('workflow-resume', new WorkflowStart($this->getNodes(), $this->getEdges()));
        $interrupt = $this->persistence->load($this->workflowId);

        if ($interrupt === null) {
            throw new WorkflowException("No saved workflow found for ID: {$this->workflowId}");
        }

        $state = $interrupt->getState();
        $currentNode = $interrupt->getCurrentNode();

        $result = $this->execute(
            $currentNode,
            $state,
            true,
            $humanFeedback
        );
        $this->notify('workflow-end', new WorkflowEnd($result));

        return  $result;
    }

    /**
     * @return Node[]
     */
    public function nodes(): array
    {
        return [];
    }

    /**
     * @return Edge[]
     */
    public function edges(): array
    {
        return [];
    }

    public function addNode(NodeInterface $node): self
    {
        $this->nodes[$node::class] = $node;
        return $this;
    }

    /**
     * @param NodeInterface[] $nodes
     */
    public function addNodes(array $nodes): Workflow
    {
        foreach ($nodes as $node) {
            $this->addNode($node);
        }
        return $this;
    }

    /**
     * @return array<string, NodeInterface>
     */
    public function getNodes(): array
    {
        if (empty($this->nodes)) {
            foreach ($this->nodes() as $node) {
                $this->addNode($node);
            }
        }

        return $this->nodes;
    }

    public function addEdge(Edge $edge): self
    {
        $this->edges[] = $edge;
        return $this;
    }

    /**
     * @param Edge[] $edges
     */
    public function addEdges(array $edges): Workflow
    {
        foreach ($edges as $edge) {
            $this->addEdge($edge);
        }
        return $this;
    }

    /**
     * @return Edge[]
     */
    public function getEdges(): array
    {
        if (empty($this->edges)) {
            $this->edges = $this->edges();
        }

        return $this->edges;
    }

    public function setStart(string $nodeClass): self
    {
        $this->startNode = $nodeClass;
        return $this;
    }

    public function setEnd(string $nodeClass): self
    {
        $this->endNode = $nodeClass;
        return $this;
    }

    private function findNextNode(string $currentNode, WorkflowState $state): ?string
    {
        foreach ($this->getEdges() as $edge) {
            if ($edge->getFrom() === $currentNode && $edge->shouldExecute($state)) {
                return $edge->getTo();
            }
        }

        return null;
    }

    public function getWorkflowId(): string
    {
        return $this->workflowId;
    }

    public function export(): string
    {
        return $this->exporter->export($this);
    }

    public function setExporter(ExporterInterface $exporter): Workflow
    {
        $this->exporter = $exporter;
        return $this;
    }
}
