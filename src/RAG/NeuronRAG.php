<?php

namespace NeuronAI\RAG;

use NeuronAI\NeuronAgent;
use NeuronAI\Messages\Message;
use NeuronAI\Messages\UserMessage;
use NeuronAI\RAG\VectorStore\VectorStoreInterface;

class NeuronRAG extends NeuronAgent
{
    /**
     * Instructions template.
     *
     * @var string|null
     */
    protected ?string $instructions = "Use the following pieces of context to answer the question of the user. If you don't know the answer, just say that you don't know, don't try to make up an answer.\n\n{context}.";

    public function __construct(protected VectorStoreInterface $store) {
        parent::__construct();
    }

    public function answerQuestion(string $question, int $k = 4): Message
    {
        $this->notify('agent:vectorstore:searching', $question);
        $documents = $this->searchDocuments($question, $k);
        $this->notify('agent:vectorstore:result', $documents);

        $this->notify('agent:instructions:changing', $this->instructions());
        $this->setSystemMessage($documents, $k);
        $this->notify('agent:instructions:changed', $this->instructions());

        return $this->run(
            new UserMessage($question)
        );
    }

    /**
     * Set the system message based on the context.
     *
     * @param array<Document> $documents
     * @param int $k
     * @return self
     */
    public function setSystemMessage(array $documents, int $k): self
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
        $docs = $this->store->similaritySearch($embedding, $k);

        $retrievedDocs = [];

        foreach ($docs as $doc) {
            //md5 for removing duplicates
            $retrievedDocs[\md5($doc->content)] = $doc;
        }

        return \array_values($retrievedDocs);
    }
}
