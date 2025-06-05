<?php

namespace NeuronAI\Tools\Toolkits\Zep;

use GuzzleHttp\Client;
use NeuronAI\Tools\PropertyType;
use NeuronAI\Tools\Tool;
use NeuronAI\Tools\ToolProperty;

class ZepGetMemoryTool extends Tool
{
    protected Client $client;

    protected string $url = 'https://api.getzep.com/api/v2';

    public function __construct(
        protected string $key,
        protected string $user_id,
        protected ?string $session_id = null,
    ) {
        if (is_null($this->session_id)) {
            $this->session_id = \uniqid();
        }

        parent::__construct(
            'get_memory',
            'Retrieves the memory for the current Zep session.'
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

        $this->client = new Client([
            'base_uri' => \trim($this->url, '/').'/',
            'headers' => [
                'Authorization' => "Api-Key {$this->key}",
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ]
        ]);

        $this->init();
    }

    protected function init()
    {
        // Create the user if it doesn't exist
        try {
            $this->client->get('users/'.$this->user_id);
        } catch (\Exception $exception) {
            $this->client->post('users', ['user_id' => $this->user_id]);
        }

        // Create the session if it doesn't exist
        try {
            $this->client->get('sessions/'.$this->session_id);
        } catch (\Exception $exception) {
            $this->client->post('sessions', [
                'session_id' => $this->session_id,
                'user_id' => $this->user_id,
            ]);
        }
    }

    public function __invoke(string $type)
    {

    }
}
