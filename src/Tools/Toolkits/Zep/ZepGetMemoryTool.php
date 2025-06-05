<?php

namespace NeuronAI\Tools\Toolkits\Zep;

use NeuronAI\Tools\PropertyType;
use NeuronAI\Tools\Tool;
use NeuronAI\Tools\ToolProperty;

class ZepGetMemoryTool extends Tool
{
    use HandleZepClient;

    public function __construct(
        protected string $key,
        protected string $user_id,
        protected ?string $session_id = null,
    ) {
        parent::__construct(
            'get_memory',
            'Retrieves relevant information from the user memory.'
        );

        $this->addProperty(
            new ToolProperty(
                'type',
                PropertyType::STRING,
                "The type of memory to retrieve ('context', 'messages', 'relevant_facts').",
                true,
                ['context', 'messages', 'relevant_facts']
            )
        )->setCallable($this);

        $this->init();
    }

    public function __invoke(string $type): array
    {
        $response = $this->client->get('sessions/'.$this->session_id.'/memory')
            ->getBody()->getContents();

        return \json_decode($response, true)['type'] ?? match ($type) {
            'context' => ['context' => 'No context available'],
            'messages' => ['messages' => 'No messages available'],
            'relevant_facts' => ['relevant_facts' => 'No relevant facts available'],
        };
    }
}
