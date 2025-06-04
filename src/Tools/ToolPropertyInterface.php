<?php

namespace NeuronAI\Tools;

interface ToolPropertyInterface extends \JsonSerializable
{
    /**
     * @return string
     */
    public function getName(): string;

    /**
     * @return PropertyType
     */
    public function getType(): PropertyType;

    /**
     * @return string
     */
    public function getDescription(): string;

    /**
     * @return bool
     */
    public function isRequired(): bool;
}
