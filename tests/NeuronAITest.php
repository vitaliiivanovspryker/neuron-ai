<?php

namespace NeuronAI\Tests;


use NeuronAI\Agent;
use NeuronAI\AgentInterface;
use NeuronAI\RAG\RAG;
use PHPUnit\Framework\TestCase;

class NeuronAITest extends TestCase
{
    /**
     * Sets up the fixture, for example, open a network connection.
     * This method is called before a test is executed.
     *
     * @throws \Exception
     */
    public function setUp(): void
    {
    }

    public function testAgentInstance()
    {
        $neuron = new Agent();
        $this->assertInstanceOf(AgentInterface::class, $neuron);

        $neuron = new RAG();
        $this->assertInstanceOf(Agent::class, $neuron);
    }
}
