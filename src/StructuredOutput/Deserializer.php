<?php

namespace NeuronAI\StructuredOutput;

use NeuronAI\Exceptions\NeuronException;
use Symfony\Component\PropertyInfo\Extractor\PhpDocExtractor;
use Symfony\Component\PropertyInfo\Extractor\ReflectionExtractor;
use Symfony\Component\PropertyInfo\PropertyInfoExtractor;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ArrayDenormalizer;
use Symfony\Component\Serializer\Normalizer\BackedEnumNormalizer;
use Symfony\Component\Serializer\Normalizer\GetSetMethodNormalizer;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Normalizer\PropertyNormalizer;
use Symfony\Component\Serializer\Serializer;

/**
 * Inspired by: https://github.com/cognesy/instructor-php
 */
class Deserializer
{
    protected Serializer $serializer;

    public function __construct()
    {
        $typeExtractor = $this->defaultTypeExtractor();

        $this->serializer = new Serializer(
            normalizers: [
                new BackedEnumNormalizer(),
                new ObjectNormalizer(propertyTypeExtractor: $typeExtractor),
                new PropertyNormalizer(propertyTypeExtractor: $typeExtractor),
                new GetSetMethodNormalizer(propertyTypeExtractor: $typeExtractor),
                new ArrayDenormalizer(),
            ],
            encoders: [new JsonEncoder()]
        );
    }

    public function fromJson(string $jsonData, string $responseModel): mixed
    {
        try {
            return $this->serializer->deserialize($jsonData, $responseModel, 'json');
        } catch (\Throwable $exception) {
            throw new NeuronException($exception->getMessage() . " - Data: {$jsonData} - Class: {$responseModel}");
        }
    }

    protected function defaultTypeExtractor(): PropertyInfoExtractor
    {
        $phpDocExtractor = new PhpDocExtractor();
        $reflectionExtractor = new ReflectionExtractor();
        return new PropertyInfoExtractor(
            listExtractors: [$reflectionExtractor],
            typeExtractors: [$phpDocExtractor, $reflectionExtractor],
            descriptionExtractors: [$phpDocExtractor],
            accessExtractors: [$reflectionExtractor],
            initializableExtractors: [$reflectionExtractor],
        );
    }
}
