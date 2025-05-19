<?php

namespace NeuronAI;

use GuzzleHttp\Client;

trait HasGuzzleClient
{
    protected Client $client;

    public function setClient(Client $client): mixed
    {
        $this->client = $client;
        return $this;
    }
}
