<?php

namespace NeuronAI\Providers\Anthropic;

use GuzzleHttp\Exception\GuzzleException;
use NeuronAI\Exceptions\ProviderException;
use Psr\Http\Message\StreamInterface;

trait HandleStream
{

    /**
     * @throws ProviderException
     * @throws GuzzleException
     */
    public function stream(array|string $messages, callable $executeToolsCallback): \Generator
    {
        $mapper = new MessageMapper($messages);

        $json = \array_filter([
            'stream' => true,
            'model' => $this->model,
            'max_tokens' => $this->max_tokens,
            'stop_sequences' => $this->stop_sequences,
            'temperature' => $this->temperature,
            'system' => $this->system ?? null,
            'messages' => $mapper->map(),
        ]);

        if (!empty($this->tools)) {
            $json['tools'] = $this->generateToolsPayload();
        }

        // https://docs.anthropic.com/claude/reference/messages_post
        $stream = $this->client->post('v1/messages', compact('json'))->getBody();

        $toolCalls = [];

        while (! $stream->eof()) {
            if (!$line = $this->parseNextDataLine($stream)) {
                continue;
            }

            // Tool calls detection (https://docs.anthropic.com/en/api/messages-streaming#streaming-request-with-tool-use)
            if (
                (\array_key_exists('content_block', $line) && $line['content_block']['type'] === 'tool_use') ||
                (\array_key_exists('content_block_delta', $line) && $line['delta']['type'] === 'input_json_delta')
            ) {
                $toolCalls = $this->composeToolCalls($line, $toolCalls);
                continue;
            }

            // Handle tool call
            if ($line['type'] === 'message_stop' && !empty($toolCalls)) {
                yield from $executeToolsCallback(
                    $this->createToolMessage($toolCalls)
                );
            }

            // Process regular content
            $content = $line['completion'];

            yield $content;
        }
    }

    /**
     * Recreate the tool_call format of anthropic API from streaming.
     *
     * @param  array<string, mixed>  $line
     * @param  array<int, array<string, mixed>>  $toolCalls
     * @return array<int, array<string, mixed>>
     */
    protected function composeToolCalls(array $line, array $toolCalls): array
    {
        if (!\array_key_exists($line['index'], $toolCalls)) {
            $toolCalls[$line['index']] = [
                'id' => $line['content_block']['id'],
                'name' => $line['content_block']['name'],
                'input' => $line['content_block']['input']??[],
            ];
        } else {
            if ($input = $line['delta']['partial_json']??null) {
                $toolCalls[$line['index']]['input'] .= $input;
            }
        }

        return $toolCalls;
    }

    protected function parseNextDataLine(StreamInterface $stream): ?array
    {
        $line = $this->readLine($stream);

        if (! \str_starts_with($line, 'data:')) {
            return null;
        }

        $line = \trim(\substr($line, \strlen('data: ')));

        try {
            return \json_decode($line, true, flags: JSON_THROW_ON_ERROR);
        } catch (\Throwable $exception) {
            throw new ProviderException('Anthropic streaming error - '.$exception->getMessage());
        }
    }

    protected function readLine(StreamInterface $stream): string
    {
        $buffer = '';

        while (! $stream->eof()) {
            $byte = $stream->read(1);

            if ($byte === '') {
                return $buffer;
            }

            $buffer .= $byte;

            if ($byte === "\n") {
                break;
            }
        }

        return $buffer;
    }
}
