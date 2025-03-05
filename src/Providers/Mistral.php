<?php

namespace NeuronAI\Providers;

use GuzzleHttp\RequestOptions;
use NeuronAI\Exceptions\ProviderException;
use NeuronAI\Chat\Messages\AssistantMessage;
use NeuronAI\Chat\Messages\Message;
use GuzzleHttp\Client;
use NeuronAI\Chat\Messages\UserMessage;

class Mistral extends OpenAI
{
    protected string $baseUri = 'https://api.mistral.ai';
}
