<?php

namespace NeuronAI;

use NeuronAI\Chat\Messages\Message;
use NeuronAI\Exceptions\AgentException;
use NeuronAI\Exceptions\SchemaException;
use NeuronAI\Schema\Deserializer;

trait HandleStructured
{
    public function structured(
        Message|array $messages,
        string $responseModel,
        int $maxRetry = 1
    ): mixed {
        // Transform the input object into a JSON schema

        // instruct the model to follow the output JSON schema

        $errors = [];
        do {
            // Call the LLM eventually adding the error message from previous calls

            // Check LLM response it's a valid JSON object

            // Deserialize the JSON response from the LLM into the response model
            try {
                $deserializer = new Deserializer();
                $output = $deserializer->fromJson('', $responseModel);

                // Validate ?? (Isn't the step before a validation?)

                // Return a hydrated instance of the response model
                if ($output instanceof $responseModel) {
                    return $output;
                }
                $errors[] = "It was impossible to create an instance of the object from the response format.";
            } catch (SchemaException $exception) {
                $errors[] = $exception->getMessage();
            }

            // If something went wrong, retry informing the model of the errors
            $maxRetry--;
        } while ($maxRetry>=0);

        throw new AgentException("The model didn't return a valid structured message.");
    }
}
