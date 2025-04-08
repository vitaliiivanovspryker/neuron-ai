<?php

namespace NeuronAI;

use NeuronAI\Chat\Messages\Message;
use NeuronAI\Chat\Messages\UserMessage;
use NeuronAI\Events\InferenceStart;
use NeuronAI\Events\InferenceStop;
use NeuronAI\Events\MessageSaved;
use NeuronAI\Events\MessageSaving;
use NeuronAI\Exceptions\AgentException;
use NeuronAI\StructuredOutput\Deserializer;
use NeuronAI\StructuredOutput\JsonExtractor;
use Spiral\JsonSchemaGenerator\Generator;
use Symfony\Component\Validator\Mapping\Loader\AttributeLoader;
use Symfony\Component\Validator\Validation;

trait HandleStructured
{
    /**
     * Enforce a structured response.
     *
     * @param Message|array $messages
     * @param string $class
     * @param int $maxRetry
     * @return mixed
     * @throws AgentException
     */
    public function structured(Message|array $messages, string $class, int $maxRetry = 1): mixed
    {
        $this->notify('structured-start');

        $messages = is_array($messages) ? $messages : [$messages];

        foreach ($messages as $lastMessage) {
            $this->notify('message-saving', new MessageSaving($lastMessage));
            $this->resolveChatHistory()->addMessage($lastMessage);
            $this->notify('message-saved', new MessageSaved($lastMessage));
        }

        // Get the JSON schema from the response model
        // https://github.com/spiral/json-schema-generator
        $schema = [
            'type' => 'object',
            ...(new Generator())->generate($class)->jsonSerialize(),
            'additionalProperties' => false,
        ];

        $error = '';
        do {
            // Eventually add the error message from previous calls
            if (!empty(trim($error))) {
                $correctionMessage = new UserMessage(
                    "There was a problem in your previous response that generated the following errors".
                    PHP_EOL.PHP_EOL.'- '.$error.PHP_EOL.PHP_EOL.
                    "Try to generate the correct JSON structure based on the provided schema."
                );
                $this->notify('message-saving', new MessageSaving($correctionMessage));
                $this->resolveChatHistory()->addMessage($correctionMessage);
                $this->notify('message-saved', new MessageSaved($correctionMessage));
            }

            $messages = $this->resolveChatHistory()->getMessages();
            $lastMessage = \end($messages);

            $this->notify(
                'inference-start',
                new InferenceStart($lastMessage)
            );

            // Call the LLM structured interface
            $response = $this->provider()
                ->systemPrompt($this->instructions())
                ->setTools($this->tools())
                ->structured(
                    $messages,
                    $class,
                    $schema
                );

            $this->notify(
                'inference-stop',
                new InferenceStop($lastMessage, $response)
            );

            try {
                // Try to extract a valid JSON object from the LLM response
                $extractor = new JsonExtractor();
                if (!$json = $extractor->getJson($response->getContent())) {
                    throw new AgentException("The response does not contains a valid JSON Object.");
                }

                // Deserialize the JSON response from the LLM into an instance of the response model
                $deserializer = new Deserializer();
                $obj = $deserializer->fromJson($json, $class);

                // Validate if the object fields respect the validation attributes
                // https://symfony.com/doc/current/validation.html#constraints
                $validator = Validation::createValidatorBuilder()
                    ->addLoader(new AttributeLoader())
                    ->getValidator();
                $validator->validate($obj);

                // Return a hydrated instance of the response model
                if ($obj instanceof $class) {
                    $this->notify('structured-stop');
                    return $obj;
                }

                $error = "It was impossible to create an instance of the original class because of a wrong response format.";
            } catch (\Exception $exception) {
                $error = $exception->getMessage();
            }

            // If something goes wrong, retry informing the model of the error
            $maxRetry--;
        } while ($maxRetry>=0);

        throw new AgentException("The model didn't return a valid structured message for {$class}.");
    }
}
