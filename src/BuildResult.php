<?php

namespace SymfonyDocsBuilder;

use Doctrine\RST\Builder;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\Filesystem\Filesystem;
use SymfonyDocsBuilder\BuildConfig;
use SymfonyDocsBuilder\CI\MissingFilesChecker;
use SymfonyDocsBuilder\ConfigFileParser;
use SymfonyDocsBuilder\Generator\HtmlForPdfGenerator;
use SymfonyDocsBuilder\Generator\JsonGenerator;
use SymfonyDocsBuilder\KernelFactory;

class BuildResult
{
    private $errors;

    public function __construct(array $errors)
    {
        $this->errors = $errors;
    }

    public function appendError(string $errorMessage): void
    {
        $this->errors[] = $errorMessage;
    }

    public function prependError(string $errorMessage): void
    {
        $this->errors = array_merge([$errorMessage], $this->errors);
    }

    public function isSuccessful(): bool
    {
        return null === $this->errors || 0 === \count($this->errors);
    }

    public function getErrors(): array
    {
        return $this->errors;
    }
}
