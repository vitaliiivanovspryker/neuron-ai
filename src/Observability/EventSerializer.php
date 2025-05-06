<?php declare(strict_types=1);

namespace NeuronAI\Observability;

use NeuronAI\Observability\Events;

class EventSerializer
{
    /** @return array<mixed,mixed> */
    public function toArray(mixed $data): array
    {
        if ($data === null) {
            return [];
        }

        if (is_array($data)) {
            return $data;
        }

        if (!is_object($data)) {
            return ['data' => $data];
        }

        return match (get_class($data)) {
            Events\AgentError::class => [
                'error' => $data->exception->getMessage(),
            ],
            Events\Deserializing::class,
            Events\Deserialized::class => [
                'class' => $data->class
            ],
            Events\Extracting::class => [
                'message' => $data->message->jsonSerialize(),
            ],
            Events\Extracted::class => [
                'message' => $data->message->jsonSerialize(),
                'schema' => $data->schema,
                'json' => $data->json,
            ],
            Events\InferenceStart::class,
            Events\MessageSaving::class,
            Events\MessageSaved::class => [
                'message' => $data->message->jsonSerialize(),
            ],
            Events\InferenceStop::class => [
                'message' => $data->message->jsonSerialize(),
                'response' => $data->response->jsonSerialize(),
            ],
            Events\InstructionsChanging::class => [
                'instructions' => $data->instructions,
            ],
            Events\InstructionsChanged::class => [
                'previous' => $data->previous,
                'current' => $data->current,
            ],
            Events\ToolCalling::class,
            Events\ToolCalled::class => [
                'tool' => $data->tool->jsonSerialize(),
            ],
            Events\Validating::class => [
                'class' => $data->class,
                'json' => $data->class,
            ],
            Events\Validated::class => [
                'class' => $data->class,
                'json' => $data->class,
                'violations' => $data->violations,
            ],
            Events\VectorStoreSearching::class => [
                'question' => $data->question->jsonSerialize(),
            ],
            Events\VectorStoreResult::class => [
                'question' => $data->question->jsonSerialize(),
                'documents' => $data->documents,
            ],
            default => [],
        };
    }
}
