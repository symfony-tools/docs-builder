<?php

namespace SymfonyDocsBuilder;

use Doctrine\RST\Builder;
use Doctrine\RST\Meta\Metas;
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
    private $metas;
    private $jsonResults = [];

    public function __construct(array $errors, Metas $metas)
    {
        $this->errors = $errors;
        $this->metas = $metas;
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

    public function getMetas(): Metas
    {
        return $this->metas;
    }

    /**
     * Returns the JSON data generated for each file, keyed by the source filename.
     *
     * @return array[]
     */
    public function getJsonResults(): array
    {
        return $this->jsonResults;
    }

    public function setJsonResults(array $jsonResults): void
    {
        $this->jsonResults = $jsonResults;
    }
}
