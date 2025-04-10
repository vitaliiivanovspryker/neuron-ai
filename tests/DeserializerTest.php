<?php

namespace NeuronAI\Tests;

use NeuronAI\StructuredOutput\Deserializer;
use NeuronAI\Tests\Utils\Person;
use PHPUnit\Framework\TestCase;

class DeserializerTest extends TestCase
{
    public function test_deserialize()
    {
        $json = '{"firstName": "John", "lastName": "Doe"}';

        $obj = (new Deserializer())->fromJson($json, Person::class);

        $this->assertInstanceOf(Person::class, $obj);
        $this->assertEquals('John', $obj->firstName);
        $this->assertEquals('Doe', $obj->lastName);
    }
}
