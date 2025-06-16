<?php

namespace NeuronAI\Workflow;

use NeuronAI\Exceptions\WorkflowException;
use ReflectionClass;

class Workflow
{
    private array $nodes = [];
    private array $edges = [];
    private ?string $startNode = null;
    private ?string $endNode = null;
    private ExporterInterface $exporter;

    public function __construct(?ExporterInterface $exporter = null)
    {
        $this->exporter = $exporter ?? new MermaidExporter();
    }

    public function addNode(NodeInterface $node): self
    {
        $name = $this->getNodeName($node);
        $this->nodes[$name] = $node;
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

    private function getNodeName(NodeInterface $node): string
    {
        $reflection = new ReflectionClass($node);
        return $reflection->getShortName();
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

    public function setStart(string $nodeClass): self
    {
        $this->startNode = $this->getShortClassName($nodeClass);
        return $this;
    }

    public function setEnd(string $nodeClass): self
    {
        $this->endNode = $this->getShortClassName($nodeClass);
        return $this;
    }

    private function getShortClassName(string $fullyQualifiedClass): string
    {
        $reflection = new ReflectionClass($fullyQualifiedClass);
        return $reflection->getShortName();
    }

    public function validate(): void
    {
        if ($this->startNode === null) {
            throw new WorkflowException('Start node must be defined');
        }

        if ($this->endNode === null) {
            throw new WorkflowException('End node must be defined');
        }

        if (!isset($this->nodes[$this->startNode])) {
            throw new WorkflowException("Start node '{$this->startNode}' does not exist");
        }

        if (!isset($this->nodes[$this->endNode])) {
            throw new WorkflowException("End node '{$this->endNode}' does not exist");
        }

        foreach ($this->edges as $edge) {
            if (!isset($this->nodes[$edge->getFrom()])) {
                throw new WorkflowException("Edge from node '{$edge->getFrom()}' does not exist");
            }

            if (!isset($this->nodes[$edge->getTo()])) {
                throw new WorkflowException("Edge to node '{$edge->getTo()}' does not exist");
            }
        }
    }

    public function execute(?WorkflowState $initialState = null): WorkflowState
    {
        $this->validate();

        $state = $initialState ?? new WorkflowState();
        $currentNode = $this->startNode;

        while ($currentNode !== $this->endNode) {
            $node = $this->nodes[$currentNode];
            $state = $node->run($state);

            $nextNode = $this->findNextNode($currentNode, $state);

            if ($nextNode === null) {
                throw new WorkflowException("No valid edge found from node '{$currentNode}'");
            }

            $currentNode = $nextNode;
        }

        $endNode = $this->nodes[$this->endNode];
        return $endNode->run($state);
    }

    private function findNextNode(string $currentNode, WorkflowState $state): ?string
    {
        foreach ($this->edges as $edge) {
            if ($edge->getFrom() === $currentNode && $edge->shouldExecute($state)) {
                return $edge->getTo();
            }
        }

        return null;
    }

    public function export(): string
    {
        return $this->exporter->export($this);
    }

    public function getEdges(): array
    {
        return $this->edges;
    }

    public function getNodes(): array
    {
        return $this->nodes;
    }
}
