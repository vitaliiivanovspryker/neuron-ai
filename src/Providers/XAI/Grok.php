<?php

namespace NeuronAI\Providers\XAI;

use NeuronAI\Providers\OpenAI\OpenAI;

class Grok extends OpenAI
{
    protected string $baseUri = 'https://api.x.ai/v1/';
}
