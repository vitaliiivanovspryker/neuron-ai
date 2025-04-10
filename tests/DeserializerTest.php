<?php

namespace NeuronAI\Tests;

use NeuronAI\StructuredOutput\Deserializer;
use NeuronAI\Tests\Utils\Person;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\Mapping\Loader\AttributeLoader;
use Symfony\Component\Validator\Validation;

class DeserializerTest extends TestCase
{
    public function test_deserialize()
    {
        $json = '{"firstNam": "Valerio", "lastName": "Barbera"}';

        $obj = (new Deserializer())->fromJson($json, Person::class);

        $this->assertInstanceOf(Person::class, $obj);

        /** @var array<ConstraintViolation> $result */
        $result = Validation::createValidatorBuilder()
            ->addLoader(new AttributeLoader())
            ->getValidator()
            ->validate($obj);

        var_dump($result[0]->getPropertyPath() . $result[0]->getMessage());
    }
}
