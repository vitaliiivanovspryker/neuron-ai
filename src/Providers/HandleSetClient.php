<?php

namespace NeuronAI\Providers;

use GuzzleHttp\Client;

trait HandleSetClient
{
    public function setClient(Client $client): static
    {
        $this->client = $client;

        return $this;
    }
}
