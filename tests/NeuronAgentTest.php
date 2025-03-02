<?php

namespace NeuronAI\Tests;


use App\Extensions\NeuronAI\Agent\NeuronAgent;
use PHPUnit\Framework\TestCase;

class NeuronAgentTest extends TestCase
{
    /**
     * @var NeuronAgent
     */
    public $neuron;

    /**
     * Sets up the fixture, for example, open a network connection.
     * This method is called before a test is executed.
     *
     * @throws \Exception
     */
    public function setUp(): void
    {
        $this->neuron = new NeuronAgent();
    }

    public function testInstance()
    {
        $this->assertInstanceOf(NeuronAgent::class, $this->neuron);
        $this->assertInstanceOf(\SplObserver::class, $this->neuron);
    }
}
