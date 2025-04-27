<?php

namespace NeuronAI\Providers\Gemini;

use GuzzleHttp\Exception\GuzzleException;
use NeuronAI\Chat\Messages\Message;
use NeuronAI\Chat\Messages\Usage;

trait HandleChat
{
    /**
     * Send a message to the LLM.
     *
     * @param Message|array<Message> $messages
     * @throws GuzzleException
     */
    public function chat(array $messages): Message
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

        $result = $this->client->post("{$this->model}:generateContent", compact('json'))
            ->getBody()->getContents();

        $result = \json_decode($result, true);

        $content = $result['candidates'][0]['contents'][0];

        if (\array_key_exists('functionCall', $content) && !empty($content['functionCall'])) {
            $response = $this->createToolCallMessage($content);
        } else {
            $response = new Message($content['role'], $content['parts'][0]['text']??'');
        }

        // Attach the usage for the current interaction
        if (\array_key_exists('usageMetadata', $result)) {
            $response->setUsage(
                new Usage(
                    $result['usageMetadata']['promptTokenCount'],
                    $result['usageMetadata']['candidatesTokenCount']
                )
            );
        }

        return $response;
    }
}
