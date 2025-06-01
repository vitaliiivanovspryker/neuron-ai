<?php

declare(strict_types=1);

namespace NeuronAI\Tests\Workflow;

use NeuronAI\Exceptions\StateGraphError;
use NeuronAI\Workflow\StateGraph;
use PHPUnit\Framework\TestCase;

class StateGraphTest extends TestCase
{
    public function test_construction(): void
    {
        $graph = new StateGraph();
        $this->assertTrue($graph->nodeExists(StateGraph::START_NODE));
        $this->assertTrue($graph->nodeExists(StateGraph::END_NODE));
    }

    public function test_add_node(): void
    {
        $nodeName = 'foo';

        $graph = new StateGraph();

        $this->assertFalse($graph->nodeExists($nodeName));

        $graph->addNode($nodeName, new TestNode());

        $this->assertTrue($graph->nodeExists($nodeName));
    }

    public function test_add_duplicate_node_fails(): void
    {
        $nodeName = 'foo';
        $this->expectException(StateGraphError::class);
        $graph = new StateGraph();
        $graph->addNode($nodeName, new TestNode());
        $graph->addNode($nodeName, new TestNode());
    }

    public function test_add_edge(): void
    {
        $graph = new StateGraph();

        $this->assertEmpty($graph->getSuccessors(StateGraph::START_NODE));

        $graph->addEdge(StateGraph::START_NODE, StateGraph::END_NODE);

        $this->assertContains(StateGraph::END_NODE, $graph->getSuccessors(StateGraph::START_NODE));

        $graph->addNode('foo', new TestNode());
        $graph->addEdge(StateGraph::START_NODE, 'foo');
        $graph->addEdge('foo', StateGraph::END_NODE);

        $successors = $graph->getSuccessors(StateGraph::START_NODE);
        $this->assertContains(StateGraph::END_NODE, $successors);
        $this->assertContains('foo', $successors);
    }

    public function test_get_node(): void
    {
        $agent = new TestNode('foobar');
        $graph = (new StateGraph())->addNode('a', $agent);
        $this->assertEquals($agent, $graph->getNode('a'));
    }

    public function test_get_start_node_fails(): void
    {
        $this->expectException(StateGraphError::class);
        (new StateGraph())->getNode(StateGraph::START_NODE);
    }

    public function test_get_end_node_fails(): void
    {
        $this->expectException(StateGraphError::class);
        (new StateGraph())->getNode(StateGraph::END_NODE);
    }

    public function test_get_unexisting_node_fails(): void
    {
        $this->expectException(StateGraphError::class);
        (new StateGraph())->getNode('a');
    }

    public function test_get_node_names(): void
    {
        $this->assertEqualsCanonicalizing(
            [StateGraph::START_NODE, 'a', 'b', 'c', StateGraph::END_NODE],
            $this->getAcyclicGraph()->getNodeNames(),
        );
    }

    public function test_get_predecessors(): void
    {
        $graph = $this->getAcyclicGraph();

        $this->assertEmpty($graph->getPredecessors(StateGraph::START_NODE));
        $this->assertEquals([StateGraph::START_NODE], $graph->getPredecessors('a'));
        $this->assertEquals(['a', 'c'], $graph->getPredecessors('b'));
        $this->assertEquals([StateGraph::START_NODE, 'a'], $graph->getPredecessors('c'));
        $this->assertEquals(['b'], $graph->getPredecessors(StateGraph::END_NODE));
    }

    public function test_add_invalid_edge_fails(): void
    {
        $this->expectException(StateGraphError::class);
        $graph = new StateGraph();
        $graph->addEdge('foo', 'bar');
    }

    public function test_add_edge_to_the_start_node_fails(): void
    {
        $this->expectException(StateGraphError::class);
        $graph = new StateGraph();
        $graph
            ->addNode('a', new TestNode())
            ->addEdge('a', StateGraph::START_NODE);
    }

    public function test_add_edge_from_the_end_node_fails(): void
    {
        $this->expectException(StateGraphError::class);
        $graph = new StateGraph();
        $graph
            ->addNode('a', new TestNode())
            ->addEdge(StateGraph::END_NODE, 'a');
    }

    public function test_add_cyclic_edge_fails(): void
    {
        $this->expectException(StateGraphError::class);
        $graph = new StateGraph();
        $graph
            ->addNode('a', new TestNode())
            ->addEdge('a', 'a');
    }

    public function test_node_is_connected_to(): void
    {
        $graph = new StateGraph();
        $this->assertFalse($graph->nodeIsConnectedTo(StateGraph::START_NODE, StateGraph::END_NODE));

        $graph->addEdge(StateGraph::START_NODE, StateGraph::END_NODE);
        $this->assertTrue($graph->nodeIsConnectedTo(StateGraph::START_NODE, StateGraph::END_NODE));
    }

    public function test_node_depends_on(): void
    {
        $graph = $this->getAcyclicGraph();
        $graph
            ->addNode('d', new TestNode())
            ->addEdge('c', 'd');

        $this->assertTrue($graph->nodeDependsOn(StateGraph::END_NODE, StateGraph::START_NODE));
        $this->assertTrue($graph->nodeDependsOn(StateGraph::END_NODE, 'b'));
        $this->assertTrue($graph->nodeDependsOn('b', 'a'));
        $this->assertTrue($graph->nodeDependsOn('b', 'c'));
        $this->assertTrue($graph->nodeDependsOn('d', 'c'));

        $this->assertFalse($graph->nodeDependsOn(StateGraph::END_NODE, 'd'));
        $this->assertFalse($graph->nodeDependsOn('b', 'd'));
    }

    public function test_node_is_connected_to_with_complex_graph(): void
    {

        $graph = new StateGraph();
        $graph
            ->addNode('a', new TestNode())
            ->addNode('b', new TestNode())
            ->addNode('c', new TestNode())
            ->addNode('d', new TestNode())
            ->addNode('e', new TestNode())
            ->addNode('f', new TestNode())
            ->addEdge('a', 'b')
            ->addEdge('b', 'c')
            ->addEdge('d', 'a')
            ->addEdge('d', 'e')
            ->addEdge('e', 'f');

        $this->assertTrue($graph->nodeIsConnectedTo('a', 'c'));
        $this->assertTrue($graph->nodeIsConnectedTo('d', 'c'));
        $this->assertTrue($graph->nodeIsConnectedTo('d', 'e'));
        $this->assertTrue($graph->nodeIsConnectedTo('d', 'f'));
        $this->assertTrue($graph->nodeIsConnectedTo('e', 'f'));

        $this->assertFalse($graph->nodeIsConnectedTo('e', 'c'));
        $this->assertFalse($graph->nodeIsConnectedTo('f', 'a'));
        $this->assertFalse($graph->nodeIsConnectedTo('a', 'f'));
        $this->assertFalse($graph->nodeIsConnectedTo('f', 'c'));
        $this->assertFalse($graph->nodeIsConnectedTo('c', 'f'));
    }

    public function test_graph_is_start_connected_to_end(): void
    {
        $graph = new StateGraph();
        $this->assertFalse($graph->isStartConnectedToEnd());

        $graph->addNode('foo', new TestNode());
        $graph->addEdge(StateGraph::START_NODE, 'foo');
        $this->assertFalse($graph->isStartConnectedToEnd());

        $graph->addNode('bar', new TestNode());
        $graph->addEdge(StateGraph::START_NODE, 'bar');
        $this->assertFalse($graph->isStartConnectedToEnd());

        $graph->addEdge('foo', StateGraph::END_NODE);
        $this->assertTrue($graph->isStartConnectedToEnd());
    }

    public function test_is_cyclic(): void
    {
        $this->assertFalse($this->getSimpleGraph()->isCyclic());
        $this->assertFalse($this->getDiamondGraph()->isCyclic());
        $this->assertFalse($this->getAcyclicGraph()->isCyclic());
        $this->assertTrue($this->getCyclicGraph()->isCyclic());
    }

    public function test_compile(): void
    {
        $executionList = $this->getAcyclicGraph2()->compile();
        $this->assertEquals(['c', 'a', 'b'], $executionList);

        $executionList = $this->getAcyclicGraph()->compile();
        $this->assertEquals(['a', 'c', 'b'], $executionList);

        $graph = (new StateGraph())
            ->addNode('a', new TestNode())
            ->addNode('b', new TestNode())
            ->addEdge(StateGraph::START_NODE, 'a')
            ->addEdge(StateGraph::START_NODE, 'b')
            ->addEdge('a', StateGraph::END_NODE);

        $this->assertEquals(['a'], $graph->compile());

        $graph->addNode('c', new TestNode())->addEdge('b', 'c');

        $this->assertEquals(['a'], $graph->compile());

        $this->assertEquals(
            ['c', 'a', 'b', 'd', 'e'],
            $this->getComplexGraph()->compile()
        );
    }

    public function test_compile_with_invalid_graph_fails(): void
    {
        $this->expectException(StateGraphError::class);
        $graph = new StateGraph();
        $graph->compile();
    }

    public function test_compile_with_cyclic_graph_fails(): void
    {
        $this->expectException(StateGraphError::class);
        $this->getCyclicGraph()->compile();
    }

    public function test_to_dot(): void
    {
        $dot = <<<DOT
        digraph G {
          START -> {a,c}
          a -> {b,d}
          b -> END
          c -> a
          d -> e
          e -> END
        }
        DOT;

        $this->assertEquals($dot, $this->getComplexGraph()->toDot());
    }

    public function test_to_mermaid(): void
    {
        $dot = <<<MERMAID
        graph TD;
          START --> a & c;
          a --> b & d;
          b --> END;
          c --> a;
          d --> e;
          e --> END;

        MERMAID;

        $this->assertEquals($dot, $this->getComplexGraph()->toMermaid());
    }

    private function getSimpleGraph(): StateGraph
    {
        return (new StateGraph())
            ->addNode('a', new TestNode())
            ->addEdge(StateGraph::START_NODE, 'a')
            ->addEdge('a', StateGraph::END_NODE);
    }

    private function getDiamondGraph(): StateGraph
    {
        return (new StateGraph())
            ->addNode('a', new TestNode())
            ->addNode('b', new TestNode())
            ->addNode('c', new TestNode())
            ->addEdge(StateGraph::START_NODE, 'a')
            ->addEdge(StateGraph::START_NODE, 'c')
            ->addEdge('a', 'b')
            ->addEdge('c', 'b')
            ->addEdge('b', StateGraph::END_NODE);
    }

    private function getAcyclicGraph(): StateGraph
    {
        return (new StateGraph())
            ->addNode('a', new TestNode())
            ->addNode('b', new TestNode())
            ->addNode('c', new TestNode())
            ->addEdge(StateGraph::START_NODE, 'a')
            ->addEdge(StateGraph::START_NODE, 'c')
            ->addEdge('a', 'b')
            ->addEdge('c', 'b')
            ->addEdge('a', 'c')
            ->addEdge('b', StateGraph::END_NODE);
    }

    private function getAcyclicGraph2(): StateGraph
    {
        return (new StateGraph())
            ->addNode('a', new TestNode())
            ->addNode('b', new TestNode())
            ->addNode('c', new TestNode())
            ->addEdge(StateGraph::START_NODE, 'a')
            ->addEdge(StateGraph::START_NODE, 'c')
            ->addEdge('a', 'b')
            ->addEdge('c', 'b')
            ->addEdge('c', 'a')
            ->addEdge('b', StateGraph::END_NODE);
    }

    private function getCyclicGraph(): StateGraph
    {
        return (new StateGraph())
            ->addNode('a', new TestNode())
            ->addNode('b', new TestNode())
            ->addNode('c', new TestNode())
            ->addEdge(StateGraph::START_NODE, 'a')
            ->addEdge('a', 'b')
            ->addEdge('b', 'c')
            ->addEdge('c', 'a')
            ->addEdge('b', StateGraph::END_NODE);
    }

    private function getComplexGraph(): StateGraph
    {
        return (new StateGraph())
            ->addNode('a', new TestNode())
            ->addNode('b', new TestNode())
            ->addNode('c', new TestNode())
            ->addNode('d', new TestNode())
            ->addNode('e', new TestNode())
            ->addEdge(StateGraph::START_NODE, 'a')
            ->addEdge(StateGraph::START_NODE, 'c')
            ->addEdge('c', 'a')
            ->addEdge('a', 'b')
            ->addEdge('a', 'd')
            ->addEdge('d', 'e')
            ->addEdge('b', StateGraph::END_NODE)
            ->addEdge('e', StateGraph::END_NODE);
    }
}
