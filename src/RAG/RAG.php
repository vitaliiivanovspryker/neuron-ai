<?php

namespace NeuronAI\RAG;

use NeuronAI\Agent;
use NeuronAI\AgentInterface;
use NeuronAI\Chat\Messages\Message;
use NeuronAI\Exceptions\AgentException;
use NeuronAI\Observability\Events\InstructionsChanged;
use NeuronAI\Observability\Events\InstructionsChanging;
use NeuronAI\Observability\Events\PostProcessed;
use NeuronAI\Observability\Events\PostProcessing;
use NeuronAI\Observability\Events\VectorStoreResult;
use NeuronAI\Observability\Events\VectorStoreSearching;
use NeuronAI\Exceptions\MissingCallbackParameter;
use NeuronAI\Exceptions\ToolCallableNotSet;
use NeuronAI\Providers\AIProviderInterface;
use NeuronAI\RAG\PostProcessor\PostProcessorInterface;

/**
 * @method RAG withProvider(AIProviderInterface $provider)
 */
class RAG extends Agent
{
    use ResolveVectorStore;
    use ResolveEmbeddingProvider;

    /**
     * @var array<PostprocessorInterface>
     */
    protected array $postProcessors = [];

    /**
     * @throws MissingCallbackParameter
     * @throws ToolCallableNotSet
     * @throws \Throwable
     */
    public function answer(Message $question): Message
    {
        $this->notify('rag-start');

        $this->retrieval($question);

        $response = $this->chat($question);

        $this->notify('rag-stop');
        return $response;
    }

    public function streamAnswer(Message $question): \Generator
    {
        $this->notify('rag-start');

        $this->retrieval($question);

        yield from $this->stream($question);

        $this->notify('rag-stop');
    }

    protected function retrieval(Message $question): void
    {
        $this->notify('rag-vectorstore-searching', new VectorStoreSearching($question));
        $documents = $this->searchDocuments($question->getContent());
        $this->notify('rag-vectorstore-result', new VectorStoreResult($question, $documents));

        $documents = $this->applyPostProcessors($question, $documents);

        $originalInstructions = $this->instructions();
        $this->notify('rag-instructions-changing', new InstructionsChanging($originalInstructions));
        $this->setSystemMessage($documents);
        $this->notify('rag-instructions-changed', new InstructionsChanged($originalInstructions, $this->instructions()));
    }

    /**
     * Set the system message based on the context.
     *
     * @param array<Document> $documents
     * @return AgentInterface
     */
    protected function setSystemMessage(array $documents): AgentInterface
    {
        $context = '';
        foreach ($documents as $document) {
            $context .= $document->content.' ';
        }

        return $this->withInstructions(
            $this->instructions().PHP_EOL.PHP_EOL."# EXTRA INFORMATION AND CONTEXT".PHP_EOL.$context
        );
    }

    /**
     * Retrieve relevant documents from the vector store.
     *
     * @return array<Document>
     */
    private function searchDocuments(string $question): array
    {
        $docs = $this->resolveVectorStore()->similaritySearch(
            $this->resolveEmbeddingsProvider()->embedText($question)
        );

        $retrievedDocs = [];

        foreach ($docs as $doc) {
            //md5 for removing duplicates
            $retrievedDocs[\md5($doc->content)] = $doc;
        }

        return \array_values($retrievedDocs);
    }

    /**
     * Apply a series of postprocessors to the retrieved documents.
     *
     * @param Message $question The question to process the documents for.
     * @param array<Document> $documents The documents to process.
     * @return array<Document> The processed documents.
     */
    protected function applyPostProcessors(Message $question, array $documents): array
    {
        foreach ($this->postProcessors() as $processor) {
            $this->notify('rag-postprocessing', new PostProcessing($processor::class, $question, $documents));
            $documents = $processor->process($question, $documents);
            $this->notify('rag-postprocessed', new PostProcessed($processor::class, $question, $documents));
        }

        return $documents;
    }

    /**
     * Feed the vector store with documents.
     *
     * @param array<Document> $documents
     * @return void
     */
    public function addDocuments(array $documents): void
    {
        $this->resolveVectorStore()->addDocuments(
            $this->resolveEmbeddingsProvider()->embedDocuments($documents)
        );
    }

    /**
     * @throws AgentException
     */
    public function setPostProcessors(array $postProcessors): RAG
    {
        foreach ($postProcessors as $processor) {
            if (! $processor instanceof PostProcessorInterface) {
                throw new AgentException($processor::class." must implement PostProcessorInterface");
            }

            $this->postProcessors[] = $processor;
        }

        return $this;
    }

    /**
     * @return PostProcessorInterface[]
     */
    protected function postProcessors(): array
    {
        return $this->postProcessors;
    }
}
