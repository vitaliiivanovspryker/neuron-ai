<?php

namespace NeuronAI\Tests;

use NeuronAI\Tests\Utils\Person;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Mapping\Loader\AttributeLoader;
use Symfony\Component\Validator\Validation;

class ValidatorTest extends TestCase
{
    public function test_validate()
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
}
