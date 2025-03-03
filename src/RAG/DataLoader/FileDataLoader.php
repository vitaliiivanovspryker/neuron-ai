<?php

namespace NeuronAI\RAG\DataLoader;

use NeuronAI\RAG\Document;

class FileDataLoader implements DataLoaderInterface
{
    public function __construct(
        protected string $filePath,
        protected array $extensions = [],
    ) {}

    public function getDocuments(): array
    {
        if (! file_exists($this->filePath)) {
            return [];
        }

        // If it's a directory
        if (is_dir($this->filePath)) {
            return $this->getDocumentsFromDirectory($this->filePath);
        }

        // If it's a file
        try {
            return [$this->getDocument($this->getContentFromFile($this->filePath), $this->filePath)];
        } catch (\Throwable $exception) {
            return [];
        }
    }

    protected function getDocumentsFromDirectory(string $directory): array
    {
        $documents = [];
        // Open the directory
        if ($handle = opendir($directory)) {
            // Read the directory contents
            while (($entry = readdir($handle)) !== false) {
                $fullPath = $directory.'/'.$entry;
                if ($entry != '.' && $entry != '..') {
                    if (is_dir($fullPath)) {
                        $documents = [...$documents, ...$this->getDocumentsFromDirectory($fullPath)];
                    } else {
                        try {
                            $documents[] = $this->getDocument($this->getContentFromFile($fullPath), $entry);
                        } catch (\Throwable $exception) {}
                    }
                }
            }

            // Close the directory
            closedir($handle);
        }

        return $documents;
    }

    /**
     * Transform files to plain text.
     *
     * Supported PDF and plain text files.
     *
     * @throws \Exception
     */
    protected function getContentFromFile(string $path): string|false
    {
        $fileExtension = strtolower(pathinfo($path, PATHINFO_EXTENSION));

        return match ($fileExtension) {
            'pdf' => PdfParser::getText($path),
            default => file_get_contents($path)
        };
    }


    protected function getDocument(string $content, string $entry): mixed
    {
        $document = new Document($content);
        $document->sourceType = 'files';
        $document->sourceName = $entry;
        $document->hash = \hash('sha256', $content);

        return $document;
    }
}
