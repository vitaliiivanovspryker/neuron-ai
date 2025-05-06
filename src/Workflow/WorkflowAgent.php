<?php declare(strict_types=1);

namespace NeuronAI\Workflow;

use NeuronAI\Agent;
use NeuronAI\Chat\Messages\Message;

class WorkflowAgent extends Agent
{
    /** @var string[] */
    private array $executionList;

    /** @var array<event:string,observer:\SplObserver>[] */
    private array $observers = [];

    public function __construct(
        private readonly StateGraph $graph,
    ) {
        $this->executionList = $graph->compile();
    }

    public function chat(Message|array $messages): Message
    {
        $input = is_array($messages) ? $messages : [$messages];
        $lastReply = null;

        $this->notify('pipeline-start');

        foreach ($this->executionList as $node) {
            $agent = $this->graph->getNode($node);

            foreach ($this->observers as $observer) {
                $agent->observe($observer['observer'], $observer['event']);
            }

            $lastReply = $agent->chat($input);
            $input = array_merge($input, [$lastReply]);
        }

        $this->notify('pipeline-end');

        return $lastReply;
    }

    public function observe(\SplObserver $observer, string $event = "*"): self
    {
        $this->observers[] = [
            'event' => $event,
            'observer' => $observer,
        ];

        return parent::observe($observer, $event);
    }
}
