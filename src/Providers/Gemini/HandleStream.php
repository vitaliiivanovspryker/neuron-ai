<?php

namespace NeuronAI\Providers\Gemini;

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
        $json = [
            'contents' => $this->messageMapper()->map($messages),
            ...$this->parameters
        ];

        if (isset($this->system)) {
            $json['system_instruction'] = $this->system;
        }

        if (!empty($this->tools)) {
            $json['tools'] = $this->generateToolsPayload();
        }

        $stream = $this->client->post("{$this->model}:streamGenerateContent}", [
            'stream' => true,
            ...\compact('json')
        ])->getBody();

        $text = '';
        $toolCalls = [];

        while (! $stream->eof()) {
            if (!$line = $this->parseNextDataLine($stream)) {
                continue;
            }

            // Process tool calls
            if ($this->hasToolCalls($line)) {
                $toolCalls = $this->composeToolCalls($line, $toolCalls);

                // Handle tool calls
                if ($line['candidates'][0]['finishReason'] === 'STOP') {
                    yield from $executeToolsCallback(
                        $this->createToolCallMessage([
                            'content' => $text,
                            'tool_calls' => $toolCalls
                        ])
                    );
                }

                continue;
            }

            // Process regular content
            $content = $line['candidates'][0]['content']['parts'][0]['text']??'';
            $text .= $content;

            yield $content;
        }
    }

    /**
     * Recreate the tool_calls format from streaming Gemini API.
     */
    protected function composeToolCalls(array $line, array $toolCalls): array
    {
        $parts = $line['candidates'][0]['content']['parts']??[];

        foreach ($parts as $index => $part) {
            if (isset($part['functionCall'])) {
                $toolCalls[$index]['name'] = $part['functionCall']['name'];
                $toolCalls[$index]['arguments'] = $part['functionCall']['args']??'';
            }
        }

        return $toolCalls;
    }

    /**
     * Determines if the given line contains tool function calls.
     *
     * @param array $line The data line to check for tool function calls.
     * @return bool Returns true if the line contains tool function calls, otherwise false.
     */
    protected function hasToolCalls(array $line): bool
    {
        $parts = $line['candidates'][0]['content']['parts']??[];

        foreach ($parts as $part) {
            if (isset($part['functionCall'])) {
                return true;
            }
        }

        return false;
    }

    protected function parseNextDataLine(StreamInterface $stream): ?array
    {
        $line = $this->readLine($stream);

        if (! str_starts_with($line, 'data:')) {
            return null;
        }

        $line = trim(substr($line, strlen('data: ')));

        if ($line === '' || $line === '[DONE]') {
            return null;
        }

        try {
            return json_decode($line, true, flags: JSON_THROW_ON_ERROR);
        } catch (\Throwable $exception) {
            throw new ProviderException('Gemini Error: '.$exception->getMessage());
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
