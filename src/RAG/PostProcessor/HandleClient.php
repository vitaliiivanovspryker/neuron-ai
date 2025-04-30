<?php

namespace NeuronAI\RAG\PostProcessor;

use GuzzleHttp\Client;

trait HandleClient
{
    public function setClient(Client $client): PostProcessorInterface
    {
        $this->client = $client;
        return $this;
    }
}
