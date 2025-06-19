<?php

namespace NeuronAI\Tests\Workflow;

use NeuronAI\Workflow\Edge;
use NeuronAI\Workflow\Persistence\FilePersistence;
use NeuronAI\Workflow\Persistence\PersistenceInterface;
use NeuronAI\Workflow\Workflow;
use NeuronAI\Workflow\WorkflowInterrupt;
use NeuronAI\Workflow\WorkflowState;
use PHPUnit\Framework\TestCase;

class WorkflowPersistenceTest extends TestCase
{
    public function test_file_persistence_save()
    {
        $persistence = new FilePersistence(__DIR__);
        $this->assertInstanceOf(PersistenceInterface::class, $persistence);

        $interrupt = new WorkflowInterrupt(
            ['question' => 'test'],
            'test_node',
            new WorkflowState()
        );
        $persistence->save('id', $interrupt);
        $this->assertFileExists(__DIR__.DIRECTORY_SEPARATOR.'neuron_workflow_id.store');

        $persistence->delete('id');
        $this->assertFileDoesNotExist(__DIR__.DIRECTORY_SEPARATOR.'neuron_workflow_id.store');
    }

    public function test_file_persistence_load()
    {
        $persistence = new FilePersistence(__DIR__);

        $interrupt = new WorkflowInterrupt(
            ['question' => 'test'],
            'test_node',
            new WorkflowState()
        );
        $persistence->save('id', $interrupt);

        $persistence = new FilePersistence(__DIR__);
        $interrupt2 = $persistence->load('id');
        $this->assertEquals($interrupt, $interrupt2);
        $persistence->delete('id');
    }

    public function test_basic_interrupt_and_resume_with_file_persistence()
    {
        $persistence = new FilePersistence(__DIR__);
        $workflow = new Workflow($persistence, 'id');
        $workflow->addNodes([
            new BeforeInterruptNode(),
            new InterruptNode(),
            new AfterInterruptNode(),
        ])
            ->addEdges([
                new Edge(BeforeInterruptNode::class, InterruptNode::class),
                new Edge(InterruptNode::class, AfterInterruptNode::class),
            ])
            ->setStart(BeforeInterruptNode::class)
            ->setEnd(AfterInterruptNode::class);

        try {
            $workflow->run();
            $this->fail('Expected WorkflowInterrupt exception was not thrown');
        } catch (WorkflowInterrupt $interrupt) {
            $this->assertEquals(InterruptNode::class, $interrupt->getCurrentNode());
            $this->assertEquals([
                'question' => 'Should we continue?',
                'current_value' => 42
            ], $interrupt->getData());

            $state = $interrupt->getState();
            $this->assertEquals('interrupt', $state->get('step'));
            $this->assertEquals(42, $state->get('value'));
        }

        $result = $workflow->resume(['approved' => true]);

        $this->assertEquals('after_interrupt', $result->get('step'));
        $this->assertEquals(['approved' => true], $result->get('user_feedback'));
        $this->assertEquals(52, $result->get('final_value'));
    }
}
