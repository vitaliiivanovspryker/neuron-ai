<?php

namespace NeuronAI\Tests;

use NeuronAI\StructuredOutput\Deserializer;
use NeuronAI\Tests\Utils\Person;
use NeuronAI\Tests\Utils\PersonWithAddress;
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

    public function test_deserialize_nested_class()
    {
        $json = '{"firstName": "John", "lastName": "Doe", "address": {"city": "Rome"}}';

        $obj = (new Deserializer())->fromJson($json, PersonWithAddress::class);

        $this->assertInstanceOf(PersonWithAddress::class, $obj);
        $this->assertEquals('Rome', $obj->address->city);
    }
}
