<?php

namespace NeuronAI\Tests;

use NeuronAI\Tests\Utils\Address;
use NeuronAI\Tests\Utils\Person;
use NeuronAI\Tests\Utils\PersonWithAddress;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Mapping\Loader\AttributeLoader;
use Symfony\Component\Validator\Validation;

class ValidatorTest extends TestCase
{
    public function test_valid()
    {
        $obj = new Person();
        $obj->firstName = 'John';
        $obj->lastName = 'Doe';

        $violations = Validation::createValidatorBuilder()
            ->addLoader(new AttributeLoader())
            ->getValidator()
            ->validate($obj);

        $this->assertEquals(0, $violations->count());
    }

    public function test_not_valid()
    {
        $obj = new Person();
        $obj->lastName = 'Doe';

        $violations = Validation::createValidatorBuilder()
            ->addLoader(new AttributeLoader())
            ->getValidator()
            ->validate($obj);

        $this->assertEquals(1, $violations->count());
        $this->assertEquals('firstName', $violations->get(0)->getPropertyPath());
    }

    public function test_nested_class()
    {
        $obj = new PersonWithAddress();
        $obj->firstName = 'John';
        $obj->lastName = 'Doe';
        $obj->address = new Address();
        $obj->address->city = 'Rome';

        $violations = Validation::createValidatorBuilder()
            ->addLoader(new AttributeLoader())
            ->getValidator()
            ->validate($obj);

        $this->assertEquals(2, $violations->count());
        //$this->assertEquals('firstName', $violations->get(0)->getPropertyPath());
    }
}
