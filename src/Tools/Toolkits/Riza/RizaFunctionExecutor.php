<?php

namespace NeuronAI\Tools\Toolkits\Riza;

use GuzzleHttp\Client;
use GuzzleHttp\RequestOptions;
use NeuronAI\Tools\Tool;
use NeuronAI\Tools\ToolProperty;

class RizaFunctionExecutor extends Tool
{
    protected Client $client;

    protected string $url = 'https://api.riza.io/v1/';

    public function __construct(
        string $key,
        protected string $language = 'JavaScript', // Python, JavaScript, and TypeScript (no PHP unfortunately)
    ) {
        parent::__construct(
            "execute_{$language}_function",
            "Execute {$language} function and get the result."
        )->addProperty(
            new ToolProperty(
                'code',
                'string',
                'The function code to execute.',
                true,
            )
        )->addProperty(
            new ToolProperty(
                'input',
                'array',
                'The input arguments to execute the function.',
                false,
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

    public function __invoke(string $code, array $input = [])
    {
        $result = $this->client->post('execute-function', [
            RequestOptions::JSON => [
                'language' => $this->language,
                'code' => $code,
                'input' => $input,
            ]
        ])->getBody()->getContents();

        return \json_decode($result, true);
    }
}
