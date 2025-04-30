<?php

namespace NeuronAI\RAG\PostProcessor;

use GuzzleHttp\Client;
use GuzzleHttp\RequestOptions;
use NeuronAI\HasGuzzleClient;

class JinaRerankerPostProcessor implements PostProcessorInterface
{
    use HasGuzzleClient;

    public function __construct(
        string $apiKey,
        protected string $model = 'jina-reranker-v2-base-multilingual',
        protected int $topN = 3
    ) {
        $this->client = new Client([
            'base_uri' => 'https://api.jina.ai/v1/',
            'headers' => [
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
                'Authorization' => 'Bearer '.$apiKey,
            ],
        ]);
    }

    public function postProcess(string $question, array $documents): array
    {
        $response = $this->client->post('rerank', [
            RequestOptions::JSON => [
                'model' => $this->model,
                'query' => $question,
                'top_n' => $this->topN,
                'documents' => $documents,
                'return_documents' => false,
            ],
        ])->getBody()->getContents();

        $result = \json_decode($response, true);

        return \array_map(function ($item) use ($documents) {
            return $documents[$item['index']];
        }, $result['results']);
    }
}
