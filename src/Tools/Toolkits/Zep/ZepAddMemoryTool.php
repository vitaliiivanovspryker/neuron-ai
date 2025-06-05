<?php

namespace NeuronAI\Tools\Toolkits\Zep;

use GuzzleHttp\RequestOptions;
use NeuronAI\Tools\PropertyType;
use NeuronAI\Tools\Tool;
use NeuronAI\Tools\ToolProperty;

class ZepAddMemoryTool extends Tool
{
    use HandleZepClient;

    public function __construct(
        protected string $key,
        protected string $user_id,
        protected ?string $session_id = null,
    ) {
        parent::__construct(
            'add_memory',
            'Tool to add relevant messages to the users memories.
            You can use this tool multiple times to add multiple messages.'
        );

        $this->addProperty(
            new ToolProperty(
                'role_type',
                PropertyType::STRING,
                "The role of the message sender (e.g., 'user', 'assistant', 'system').",
                true,
            )
        )->addProperty(
            new ToolProperty(
                'content',
                PropertyType::STRING,
                "The text content of the message.",
                true,
            )
        )->setCallable($this);

        $this->init();
    }

    public function __invoke(string $role_type, string $content): string
    {
        try {
            $this->client->post('sessions/'.$this->session_id.'/memory', [
                RequestOptions::JSON => [
                    'messages' => [
                        compact('role_type', 'content')
                    ]
                ]
            ]);

            return "Message from {$role_type} has been added to memory session {$this->session_id}.";
        } catch (\Exception $exception) {
            return "Error adding the message to the memory: {$exception->getMessage()}";
        }
    }
}
