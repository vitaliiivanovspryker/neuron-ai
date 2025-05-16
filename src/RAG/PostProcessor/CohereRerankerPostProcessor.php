<?php

namespace NeuronAI\RAG\PostProcessor;

use GuzzleHttp\Client;
use GuzzleHttp\RequestOptions;
use NeuronAI\Chat\Messages\Message;
use NeuronAI\HasGuzzleClient;
use NeuronAI\RAG\Document;

class CohereRerankerPostProcessor implements PostProcessorInterface
{
    use HasGuzzleClient;

    public function __construct(
        string $apiKey,
        protected string $model = 'rerank-v3.5',
        protected int $topN = 3
    ) {
        $this->client = new Client([
            'base_uri' => 'https://api.cohere.com/v2/',
            'headers' => [
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
                'Authorization' => 'Bearer '.$apiKey,
            ],
        ]);
    }

    public function process(Message $question, array $documents): array
    {
        $response = $this->client->post('rerank', [
            RequestOptions::JSON => [
                'model' => $this->model,
                'query' => $question->getContent(),
                'top_n' => $this->topN,
                'documents' => \array_map(fn(Document $document) => $document->content, $documents),
            ],
        ])->getBody()->getContents();

        $result = \json_decode($response, true);

        return \array_map(function ($item) use ($documents) {
            $document = $documents[$item['index']];
            $document->score = $item['relevance_score'];
            return $document;
        }, $result['results']);
    }
}
