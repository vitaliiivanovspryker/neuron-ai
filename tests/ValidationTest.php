<?php

namespace NeuronAI\Tests;

use NeuronAI\StructuredOutput\Validation\Rules\ArrayOf;
use NeuronAI\StructuredOutput\Validation\Rules\IsNull;
use NeuronAI\StructuredOutput\Validation\Rules\Length;
use NeuronAI\StructuredOutput\Validation\Rules\NotBlank;
use NeuronAI\StructuredOutput\Validation\Rules\NotNull;
use NeuronAI\StructuredOutput\Validation\Rules\Url;
use NeuronAI\StructuredOutput\Validation\Validator;
use PHPUnit\Framework\TestCase;

class ValidationTest extends TestCase
{
    public function test_not_blank_validation()
    {
        $class = new class () {
            #[NotBlank(false)]
            public string $name;
        };
        $violations = Validator::validate(new $class());
        $this->assertCount(1, $violations);

        $class = new class () {
            #[NotBlank(true)]
            public string $name;
        };
        $violations = Validator::validate(new $class());
        $this->assertCount(0, $violations);
    }

    public function test_not_null_validation()
    {
        $class = new class () {
            #[NotNull]
            public string $name;
        };
        $class = new $class();

        $violations = Validator::validate(new $class());
        $this->assertCount(1, $violations);

        $class->name = 'test';
        $violations = Validator::validate($class);
        $this->assertCount(0, $violations);
    }

    public function test_is_null_validation()
    {
        $class = new class () {
            #[IsNull]
            public ?string $name;
        };
        $class = new $class();

        $violations = Validator::validate($class);
        $this->assertCount(0, $violations);

        $class->name = 'test';
        $violations = Validator::validate($class);
        $this->assertCount(1, $violations);

        $class->name = null;
        $violations = Validator::validate(new $class());
        $this->assertCount(0, $violations);
    }

    public function test_url_validation()
    {
        $class = new class () {
            #[Url]
            public string $url = 'test';
        };
        $class = new $class();

        $violations = Validator::validate($class);
        $this->assertCount(1, $violations);

        $class->url = 'https://inspector.dev';
        $violations = Validator::validate($class);
        $this->assertCount(0, $violations);
    }

    public function test_array_of_validation()
    {
        $class = new class () {
            #[ArrayOf(type: 'string')]
            public array $tags = [];
        };
        $class = new $class();

        $violations = Validator::validate($class);
        $this->assertCount(1, $violations);

        $class = new class () {
            #[ArrayOf(type: 'string', allowEmpty: true)]
            public array $tags = [];
        };
        $class = new $class();

        $violations = Validator::validate($class);
        $this->assertCount(0, $violations);

        $class = new class () {
            #[ArrayOf(type: 'string')]
            public array $tags;
        };
        $class = new $class();

        $class->tags = [123];
        $violations = Validator::validate($class);
        $this->assertCount(1, $violations);

        $class->tags = ['test'];
        $violations = Validator::validate($class);
        $this->assertCount(0, $violations);
    }

    public function test_length_validation()
    {
        $class = new class {
            #[Length(exactly: 10)]
            public string $name;
        };
        $class = new $class();

        $violations = Validator::validate($class);
        $this->assertCount(1, $violations);

        $class->name = 'xxxxx';
        $violations = Validator::validate($class);
        $this->assertCount(1, $violations);

        $class->name = 'xxxxxxxxxx';
        $violations = Validator::validate($class);
        $this->assertCount(0, $violations);

        $class = new class {
            #[Length(min: 1)]
            public string $name = 'x';
        };
        $class = new $class();

        $violations = Validator::validate($class);
        $this->assertCount(0, $violations);

        $class->name = '';
        $violations = Validator::validate($class);
        $this->assertCount(1, $violations);

        $class = new class {
            #[Length(max: 1)]
            public string $name = 'x';
        };
        $class = new $class();

        $violations = Validator::validate($class);
        $this->assertCount(0, $violations);

        $class->name = 'xx';
        $violations = Validator::validate($class);
        $this->assertCount(1, $violations);
    }
}
