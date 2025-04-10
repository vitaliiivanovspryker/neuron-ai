<?php

namespace NeuronAI\Tests;

use NeuronAI\StructuredOutput\JsonSchema;
use NeuronAI\StructuredOutput\Property;
use PHPUnit\Framework\TestCase;

class JsonSchemaTest extends TestCase
{
    public function test_all_properties_required()
    {
        $class = new class {
            public string $firstName;
            public string $lastName;
        };

        $schema = (new JsonSchema())->generate($class::class);

        $this->assertEquals([
            'type' => 'object',
            'properties' => [
                'firstName' => [
                    'type' => 'string',
                ],
                'lastName' => [
                    'type' => 'string',
                ]
            ],
            'required' => ['firstName', 'lastName']
        ], $schema);
    }
    public function test_with_nullable_properties()
    {
        $class = new class {
            public string $firstName;
            public ?string $lastName;
        };

        $schema = (new JsonSchema())->generate($class::class);

        $this->assertEquals([
            'type' => 'object',
            'properties' => [
                'firstName' => [
                    'type' => 'string',
                ],
                'lastName' => [
                    'type' => ['string', 'null'],
                ]
            ],
            'required' => ['firstName']
        ], $schema);
    }

    public function test_with_default_value()
    {
        $class = new class {
            public string $firstName;
            public ?string $lastName = 'last name';
        };

        $schema = (new JsonSchema())->generate($class::class);

        $this->assertEquals([
            'type' => 'object',
            'properties' => [
                'firstName' => [
                    'type' => 'string',
                ],
                'lastName' => [
                    'default' => 'last name',
                    'type' => ['string', 'null'],
                ]
            ],
            'required' => ['firstName']
        ], $schema);
    }

    public function test_with_attribute()
    {
        $class = new class {
            #[Property(title: "The user first name", description: "The user first name")]
            public string $firstName;
        };

        $schema = (new JsonSchema())->generate($class::class);

        $this->assertEquals([
            'type' => 'object',
            'properties' => [
                'firstName' => [
                    'title' => 'The user first name',
                    'description' => 'The user first name',
                    'type' => 'string',
                ]
            ],
            'required' => ['firstName']
        ], $schema);
    }

    public function test_nullable_property_with_attribute()
    {
        $class = new class {
            #[Property(title: "The user first name", description: "The user first name")]
            public ?string $firstName;
        };

        $schema = (new JsonSchema())->generate($class::class);

        $this->assertEquals([
            'type' => 'object',
            'properties' => [
                'firstName' => [
                    'title' => 'The user first name',
                    'description' => 'The user first name',
                    'type' => ['string', 'null'],
                ]
            ]
        ], $schema);
    }
}
