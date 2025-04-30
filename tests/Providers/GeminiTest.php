<?php

namespace NeuronAI\Tests\Providers;

use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Response;
use NeuronAI\Chat\Messages\UserMessage;
use NeuronAI\Providers\Gemini\Gemini;
use PHPUnit\Framework\TestCase;

class GeminiTest extends TestCase
{
    public function test_chat_request(): void
    {
        $sentRequests = [];
        $history = Middleware::history($sentRequests);
        $mockHandler = new MockHandler([
            new Response(
                status: 200,
                body: '
                    {
	"candidates": [
		{
			"content": {
			    "role": "model",
			    "parts": [
                    {
                        "text": "How can I assist you today?"
                    }
                ]
			}
		}
	]
}
',
            ),
        ]);
        $stack = HandlerStack::create($mockHandler);
        $stack->push($history);

        $client = new Client(['handler' => $stack]);

        $provider = (new Gemini('', 'gemini-2.0-flash'))->setClient($client);

        $response = $provider->chat([new UserMessage('Hi')]);

        // Ensure we sent one request
        $this->assertCount(1, $sentRequests);
        $request = $sentRequests[0];

        // Ensure we have sent the expected request payload.
        $expectedResponse = [
            'contents' => [
                [
                    'role' => 'user',
                    'parts' => [
                        ['text' => 'Hi']
                    ],
                ],
            ],
        ];

        $this->assertSame($expectedResponse, json_decode($request['request']->getBody()->getContents(), true));
        $this->assertSame('How can I assist you today?', $response->getContent());
    }
}
