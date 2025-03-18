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
    public function stream(array|string $messages): \Generator
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
        $response = $this->client->post('v1/messages', compact('json'));

        while (! $response->getBody()->eof()) {
            $line = $this->parseNextDataLine($response->getBody());

            // Skip empty data or DONE markers
            if ($line === null) {
                continue;
            }

            // todo: Tool calls detection here (https://docs.anthropic.com/en/api/messages-streaming#streaming-request-with-tool-use)

            // Process regular content
            $content = $line['completion'];
            $text .= $content;

            $finishReason = $this->mapFinishReason($line);

            yield $content;
        }
    }

    /**
     * @return array<string, mixed>|null Parsed JSON data or null if line should be skipped
     */
    protected function parseNextDataLine(StreamInterface $stream): ?array
    {
        $line = $this->readLine($stream);

        if (! \str_starts_with($line, 'data:')) {
            return null;
        }

        $line = \trim(\substr($line, \strlen('data: ')));

        try {
            return json_decode($line, true, flags: JSON_THROW_ON_ERROR);
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
