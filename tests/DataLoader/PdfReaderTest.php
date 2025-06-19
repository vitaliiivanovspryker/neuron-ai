<?php

namespace NeuronAI\Tests\DataLoader;

use NeuronAI\Exceptions\DataReaderException;
use NeuronAI\RAG\DataLoader\PdfReader;
use NeuronAI\RAG\DataLoader\ReaderInterface;
use PHPUnit\Framework\TestCase;

class PdfReaderTest extends TestCase
{
    public function test_instance()
    {
        $instance = new PdfReader();
        $this->assertInstanceOf(PdfReader::class, $instance);
        $this->assertInstanceOf(ReaderInterface::class, $instance);
    }

    public function test_set_pdf()
    {
        $instance = new PdfReader();

        $instance = $instance->setPdf(__DIR__ . '/test.pdf');
        $this->assertInstanceOf(PdfReader::class, $instance);
    }

    public function test_set_pdf_exception()
    {
        $instance = new PdfReader();

        $this->expectException(DataReaderException::class);
        $instance->setPdf('');
    }

    public function test_get_text()
    {
        $instance = new PdfReader();
        $text = $instance->getText(__DIR__ . '/test.pdf');
        $this->assertStringEqualsFile(__DIR__. '/target.txt', $text . PHP_EOL);
    }

    public function test_get_text_with_image()
    {
        $instance = new PdfReader();
        $text = $instance->getText(__DIR__ . '/test-with-image.pdf');
        $this->assertStringEqualsFile(__DIR__. '/target.txt', $text . PHP_EOL);
    }

    public function test_get_text_exception()
    {
        $instance = new PdfReader();

        $this->expectException(DataReaderException::class);
        $instance->getText(__DIR__.'/test.pdf', ['binPath' => 'path/to/bin']);
    }
}
