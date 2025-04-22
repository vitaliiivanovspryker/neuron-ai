<?php

namespace NeuronAI\RAG\PostProcessor;

use GuzzleHttp\Client;
use GuzzleHttp\RequestOptions;

class JinaRerankerPostProcessor
{
    protected Client $client;
    protected string $model;
    protected int $topN;

    public function __construct(string $apiKey, string $model = 'jina-reranker-v2-base-multilingual', int $topN = 3)
    {
        $this->client = new Client([
            'base_uri' => 'https://api.jina.ai/v1/',
            'headers' => [
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
                'Authorization' => 'Bearer '.$apiKey,
            ],
        ]);

        $this->model = $model;
        $this->topN = $topN;
    }

    public function postProcess(string $question, array $documents): array
    {
        return $this->rerank($question, $documents, $this->topN);
    }

    private function rerank(string $query, array $documents, int $topN): array
    {
        $response = $this->client->post('rerank', [
            RequestOptions::JSON => [
                'model' => $this->model,
                'query' => $query,
                'top_n' => $topN,
                'documents' => $documents,
                'return_documents' => false,
            ],
        ]);

        $body = $response->getBody()->getContents();
        $result = \json_decode($body, true);

        $rerankedDocuments = [];

        foreach ($result['results'] as $result) {
            $rerankedDocuments[] = $documents[$result['index']];
        }

        return $rerankedDocuments;
    }
}
