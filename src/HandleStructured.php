<?php

namespace NeuronAI;

use NeuronAI\Chat\Messages\Message;
use NeuronAI\Chat\Messages\UserMessage;
use NeuronAI\Observability\Events\Deserialized;
use NeuronAI\Observability\Events\Deserializing;
use NeuronAI\Observability\Events\Extracted;
use NeuronAI\Observability\Events\Extracting;
use NeuronAI\Observability\Events\InferenceStart;
use NeuronAI\Observability\Events\InferenceStop;
use NeuronAI\Observability\Events\MessageSaved;
use NeuronAI\Observability\Events\MessageSaving;
use NeuronAI\Exceptions\AgentException;
use NeuronAI\Observability\Events\Validated;
use NeuronAI\Observability\Events\Validating;
use NeuronAI\StructuredOutput\Deserializer;
use NeuronAI\StructuredOutput\JsonExtractor;
use NeuronAI\StructuredOutput\JsonSchema;
use Symfony\Component\Validator\ConstraintViolation;
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
     * @throws \ReflectionException
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
            ...(new JsonSchema())->generate($class),
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
                $this->notify('structured-extracting', new Extracting($response));
                $json = (new JsonExtractor())->getJson($response->getContent());
                $this->notify('structured-extracted', new Extracted($response, $json));
                if (!$json) {
                    throw new AgentException("The response does not contains a valid JSON Object.");
                }

                // Deserialize the JSON response from the LLM into an instance of the response model
                $this->notify('structured-deserializing', new Deserializing($class));
                $obj = (new Deserializer())->fromJson($json, $class);
                $this->notify('structured-deserialized', new Deserialized($class));

                // Validate if the object fields respect the validation attributes
                // https://symfony.com/doc/current/validation.html#constraints
                $this->notify('structured-validating', new Validating($class, $json));
                $violations = Validation::createValidatorBuilder()
                    ->addLoader(new AttributeLoader())
                    ->getValidator()
                    ->validate($obj);

                if ($violations->count() > 0) {
                    $info = '';
                    /** @var array<ConstraintViolation> $violation */
                    foreach ($violations as $violation) {
                        $info .= PHP_EOL.'- '.$violation->getPropertyPath().': '.$violation->getMessage();
                    }
                    throw new AgentException($info);
                }
                $this->notify('structured-validated', new Validated($class, $json));

                return $obj;
            } catch (\Exception $exception) {
                $error = $exception->getMessage();
            }

            // If something goes wrong, retry informing the model of the error
            $maxRetry--;
        } while ($maxRetry>=0);

        throw new AgentException("The model didn't return a valid structured message for {$class}.");
    }
}
