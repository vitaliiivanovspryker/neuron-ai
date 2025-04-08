<?php

namespace NeuronAI;

use NeuronAI\Chat\Messages\Message;
use NeuronAI\Chat\Messages\UserMessage;
use NeuronAI\Exceptions\AgentException;
use NeuronAI\Schema\Deserializer;
use NeuronAI\Schema\JsonExtractor;
use NeuronAI\Schema\JsonSchemaGenerator;
use Symfony\Component\Validator\Mapping\Loader\AttributeLoader;
use Symfony\Component\Validator\Validation;

trait HandleStructured
{
    /**
     * Enforce a structured response.
     *
     * @param Message|array $messages
     * @param string $responseModel
     * @param int $maxRetry
     * @return mixed
     * @throws AgentException
     */
    public function structured(
        Message|array $messages,
        string $responseModel,
        int $maxRetry = 1
    ): mixed {
        // Transform the input object into a JSON schema
        $schema = JsonSchemaGenerator::generate($responseModel);

        $error = '';
        do {
            // Eventually add the error message from previous calls
            if (!\empty(\trim($error))) {
                $this->resolveChatHistory()->addMessage(
                    new UserMessage(
                        "There was a problem in your previous response that generated the following errors".PHP_EOL.PHP_EOL.
                        '- '.$error.PHP_EOL.PHP_EOL.
                        "Try to generate the correct JSON structure based on the provided schema."
                    )
                );
            }

            // Call the LLM asking to respect the JSON schema
            $response = $this->provider()
                ->systemPrompt(
                    $this->instructions().PHP_EOL.
                    "# OUTPUT CONSTRAINTS".PHP_EOL.
                    "Your output must be a valid json object respecting the following JSON schema:".PHP_EOL.
                    \json_encode($schema)
                )
                ->setTools($this->tools())
                ->chat(
                    $this->resolveChatHistory()->getMessages()
                );

            try {
                // Try to extract a valid JSON object from the LLM response
                $extractor = new JsonExtractor();
                if (!$json = $extractor->getJson($response->getContent())) {
                    throw new AgentException("The model didn't return a valid structured message for {$responseModel}.");
                }

                // Deserialize the JSON response from the LLM into an instance of the response model
                $deserializer = new Deserializer();
                $obj = $deserializer->fromJson($json, $responseModel);

                // Validate if the object fields respect the validation attributes
                // https://symfony.com/doc/current/validation.html#constraints
                $validator = Validation::createValidatorBuilder()
                    ->addLoader(new AttributeLoader())
                    ->getValidator();
                $validator->validate($obj);

                // Return a hydrated instance of the response model
                if ($obj instanceof $responseModel) {
                    return $obj;
                }

                $error = "It was impossible to create an instance of the object from the response format.";
            } catch (\Exception $exception) {
                $error = $exception->getMessage();
            }

            // If something goes wrong, retry informing the model of the errors
            $maxRetry--;
        } while ($maxRetry>=0);

        throw new AgentException("The model didn't return a valid structured message for {$responseModel}.");
    }
}
