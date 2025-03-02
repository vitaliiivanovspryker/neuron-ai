<?php

namespace NeuronAI\Tools;

interface ToolInterface
{
    /**
     * Get the unique name of the tool.
     */
    public function getName(): string;

    /**
     * Get a description of the tool's functionality.
     */
    public function getDescription(): string;

    /**
     * Add a Property with a name, type, description, and optional required constraint.
     */
    public function addProperty(ToolProperty $property): self;

    /**
     * Get the Properties schema.
     */
    public function getProperties(): array;

    /**
     * Names of the required properties.
     *
     * @return array
     */
    public function getRequiredProperties(): array;

    /**
     * Define the code to be executed.
     *
     * @param callable $callback
     * @return mixed
     */
    public function setCallable(callable $callback): self;

    /**
     * Execute the tool's logic with input parameters.
     */
    public function execute(array $input): mixed;
}
