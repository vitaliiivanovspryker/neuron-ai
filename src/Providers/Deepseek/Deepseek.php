<?php

declare(strict_types=1);

namespace NeuronAI\Providers\Deepseek;

use NeuronAI\Providers\OpenAI\OpenAI;

class Deepseek extends OpenAI
{
    use HandleStructured;

    protected string $baseUri = "https://api.deepseek.com/v1";
}
