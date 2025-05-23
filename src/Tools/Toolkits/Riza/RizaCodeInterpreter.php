<?php

namespace NeuronAI\Tools\Toolkits\Riza;

use GuzzleHttp\Client;
use GuzzleHttp\RequestOptions;
use NeuronAI\Tools\Tool;
use NeuronAI\Tools\ToolProperty;

class RizaCodeInterpreter extends Tool
{
    protected Client $client;

    protected string $url = 'https://api.riza.io/v1/';

    public function __construct(
        string $key,
        protected string $language = 'PHP',
    ) {
        parent::__construct(
            "execute_{$language}_code",
            "Execute {$language} scripts in a secure and isolated runtime environment."
        )->addProperty(
            new ToolProperty(
                'code',
                'string',
                'The code to execute.',
                true,
            )
        )->setCallable($this);

        $this->client = new Client([
            'base_uri' => trim($this->url, '/').'/',
            'headers' => [
                'Authorization' => 'Bearer '.$key,
                'Content-Type' => 'application/json; charset=utf-8',
                'Accept' => 'application/json',
            ]
        ]);
    }

    public function __invoke(string $code)
    {
        $result = $this->client->post('execute', [
            RequestOptions::JSON => [
                'language' => $this->language,
                'code' => $code,
            ]
        ])->getBody()->getContents();

        return \json_decode($result, true);
    }
}
