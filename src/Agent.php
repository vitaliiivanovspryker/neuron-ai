<?php

namespace NeuronAI;

use NeuronAI\Chat\Messages\ToolCallResultMessage;
use NeuronAI\Observability\Events\AgentError;
use NeuronAI\Observability\Events\ToolCalled;
use NeuronAI\Observability\Events\ToolCalling;
use NeuronAI\Chat\Messages\ToolCallMessage;
use NeuronAI\Observability\Observable;

class Agent implements AgentInterface
{
    use StaticConstructor;
    use ResolveProvider;
    use ResolveTools;
    use ResolveChatHistory;
    use HandleChat;
    use HandleStream;
    use HandleStructured;
    use Observable;

    /**
     * The system instructions.
     *
     * @var string
     */
    protected string $instructions = 'Your are a helpful and friendly AI agent built with Neuron AI PHP framework.';

    protected function executeTools(ToolCallMessage $toolCallMessage): ToolCallResultMessage
    {
        $toolCallResult = new ToolCallResultMessage($toolCallMessage->getTools());

        foreach ($toolCallResult->getTools() as $tool) {
            $this->notify('tool-calling', new ToolCalling($tool));
            try {
                $tool->execute();
            } catch (\Throwable $exception) {
                $this->notify('error', new AgentError($exception));
                throw $exception;
            }
            $this->notify('tool-called', new ToolCalled($tool));
        }

        return $toolCallResult;
    }

    public function withInstructions(string $instructions): AgentInterface
    {
        $this->instructions = $instructions;
        return $this;
    }

    public function instructions(): string
    {
        return $this->instructions;
    }
}
