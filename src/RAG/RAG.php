<?php

namespace NeuronAI\RAG;

use NeuronAI\Agent;
use NeuronAI\Chat\Messages\Message;
use NeuronAI\Events\InstructionsChanged;
use NeuronAI\Events\InstructionsChanging;
use NeuronAI\Events\VectorStoreResult;
use NeuronAI\Events\VectorStoreSearching;
use NeuronAI\Exceptions\MissingCallbackParameter;
use NeuronAI\Exceptions\ToolCallableNotSet;
use NeuronAI\Providers\Embeddings\EmbeddingsProviderInterface;
use NeuronAI\RAG\VectorStore\VectorStoreInterface;

class RAG extends Agent
{
    /**
     * @var VectorStoreInterface
     */
    protected VectorStoreInterface $store;

    /**
     * The embeddings provider.
     *
     * @var EmbeddingsProviderInterface
     */
    protected EmbeddingsProviderInterface $embeddingsProvider;

    /**
     * Instructions template.
     *
     * @var string|null
     */
    protected ?string $instructions = "Use the following pieces of context to answer the question of the user. If you don't know the answer, just say that you don't know, don't try to make up an answer.\n\n{context}.";

    /**
     * @throws MissingCallbackParameter
     * @throws ToolCallableNotSet
     */
    public function answer(Message $question, int $k = 4): Message
    {
        $this->notify('rag-start');

        $this->retrieval($question, $k);

        $response = $this->chat($question);

        $this->notify('rag-stop');
        return $response;
    }

    public function streamAnswer(Message $question, int $k = 4): \Generator
    {
        $this->notify('rag-start');

        $this->retrieval($question, $k);

        yield from $this->stream($question);

        $this->notify('rag-stop');
    }

    protected function retrieval(Message $question, int $k = 4): void
    {
        $this->notify(
            'rag-vectorstore-searching',
            new VectorStoreSearching($question)
        );
        $documents = $this->searchDocuments($question->getContent(), $k);
        $this->notify(
            'rag-vectorstore-result',
            new VectorStoreResult($question, $documents)
        );

        $defaultInstructions = $this->instructions();
        $this->notify(
            'rag-instructions-changing',
            new InstructionsChanging($defaultInstructions)
        );
        $this->setSystemMessage($documents, $k);
        $this->notify(
            'rag-instructions-changed',
            new InstructionsChanged($defaultInstructions, $this->instructions())
        );
    }

    /**
     * Set the system message based on the context.
     *
     * @param array<Document> $documents
     * @param int $k
     * @return RAG
     */
    public function setSystemMessage(array $documents, int $k): RAG
    {
        $context = '';
        $i = 0;
        foreach ($documents as $document) {
            if ($i >= $k) {
                break;
            }
            $i++;
            $context .= $document->content.' ';
        }

        return $this->setInstructions(
            \str_replace('{context}', $context, $this->instructions())
        );
    }

    /**
     * Retrieve relevant documents from the vector store.
     *
     * @param string $question
     * @param int $k
     * @return array<Document>
     */
    private function searchDocuments(string $question, int $k): array
    {
        $embedding = $this->embeddings()->embedText($question);
        $docs = $this->vectorStore()->similaritySearch($embedding, $k);

        $retrievedDocs = [];

        foreach ($docs as $doc) {
            //md5 for removing duplicates
            $retrievedDocs[\md5($doc->content)] = $doc;
        }

        return \array_values($retrievedDocs);
    }

    public function setEmbeddingsProvider(EmbeddingsProviderInterface $provider): self
    {
        $this->embeddingsProvider = $provider;
        return $this;
    }

    public function embeddings(): EmbeddingsProviderInterface
    {
        return $this->embeddingsProvider;
    }

    public function setVectorStore(VectorStoreInterface $store): self
    {
        $this->store = $store;
        return $this;
    }

    public function vectorStore(): VectorStoreInterface
    {
        return $this->store;
    }
}
