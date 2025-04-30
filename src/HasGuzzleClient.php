<?php

namespace NeuronAI;

use GuzzleHttp\Client;
use NeuronAI\RAG\PostProcessor\PostProcessorInterface;

trait HasGuzzleClient
{
    protected Client $client;

    public function setClient(Client $client): mixed
    {
        $this->client = $client;
        return $this;
    }
}
