<?php

namespace NeuronAI\Tools\Toolkits\Zep;

use NeuronAI\Tools\PropertyType;
use NeuronAI\Tools\Tool;
use NeuronAI\Tools\ToolProperty;

class ZepGetSessionTool extends Tool
{
    use HandleZepClient;

    public function __construct(
        protected string $key,
        protected string $user_id,
        protected ?string $session_id = null,
    ) {
        parent::__construct(
            'get_session_memory',
            'Retrieves relevant information from the user session.'
        );

        $this->addProperty(
            new ToolProperty(
                'type',
                PropertyType::STRING,
                "The type of memory to retrieve ('context', 'messages', 'relevant_facts') from the current session.",
                false,
                ['messages', 'context', 'relevant_facts']
            )
        )->setCallable($this);

        $this->initClient()->createUser()->createSession();
    }

    public function __invoke(string $type = 'messages'): array
    {
        $response = $this->client->get('sessions/'.$this->session_id.'/memory')
            ->getBody()->getContents();

        return \json_decode($response, true)['type'] ?? match ($type) {
            'context' => ['context' => 'No context available'],
            'relevant_facts' => ['relevant_facts' => 'No relevant facts available'],
            default => ['messages' => 'No messages available'],
        };
    }
}
