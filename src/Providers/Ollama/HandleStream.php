<?php

namespace NeuronAI\Providers\Ollama;

use NeuronAI\Chat\Messages\Message;
use NeuronAI\Exceptions\ProviderException;
use Psr\Http\Message\StreamInterface;

trait HandleStream
{
    public function stream(array|string $messages, callable $executeToolsCallback): \Generator
    {
        // Include the system prompt
        if (isset($this->system)) {
            \array_unshift($messages, new Message(Message::ROLE_SYSTEM, $this->system));
        }

        $mapper = new MessageMapper($messages);

        $json = \array_filter([
            'stream' => true,
            'model' => $this->model,
            'temperature' => $this->temperature,
            'messages' => $mapper->map(),
        ]);

        if (!empty($this->tools)) {
            $json['tools'] = $this->generateToolsPayload();
        }

        $stream = $this->client->post(
            'chat', compact('json')
        )->getBody();

        $text = '';
        $toolCalls = [];

        while (! $stream->eof()) {
            if (!$line = $this->parseNextJson($stream)) {
                continue;
            }

            // Process tool calls
            if (\array_key_exists('tool_calls', $line)) {
                $toolCalls = $this->composeToolCalls($line, $toolCalls);
                continue;
            }

            // Handle tool call
            if ($line['finish_reason'] === 'tool_calls') {
                yield from $executeToolsCallback(
                    $this->createToolMessage([
                        'content' => $text,
                        'tool_calls' => $toolCalls
                    ])
                );

                return;
            }

            // Process regular content
            $content = $line['message']['content']??'';
            $text .= $content;

            yield $content;
        }
    }

    protected function parseNextJson(StreamInterface $stream): ?array
    {
        $line = $this->readLine($stream);

        if (empty($line)) {
            return null;
        }

        $json = \json_decode($line, true);

        if ($json['done']) {
            return null;
        }

        if (! isset($json['message']) || $json['message']['role'] !== 'assistant') {
            return null;
        }

        return $json;
    }

    protected function readLine(StreamInterface $stream): string
    {
        $buffer = '';

        while (! $stream->eof()) {
            if ('' === ($byte = $stream->read(1))) {
                return $buffer;
            }
            $buffer .= $byte;
            if ($byte === "\n") {
                break;
            }
        }

        return $buffer;
    }

    /**
     * Recreate the tool_calls format of openai API from streaming.
     *
     * @param  array<string, mixed>  $line
     * @param  array<int, array<string, mixed>>  $toolCalls
     * @return array<int, array<string, mixed>>
     */
    protected function composeToolCalls(array $line, array $toolCalls): array
    {
        //
    }
}
