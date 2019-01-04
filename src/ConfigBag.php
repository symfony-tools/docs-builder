<?php

namespace SymfonyDocsBuilder;

class ConfigBag
{
    private $basePath;
    private $symfonyVersion;
    private $symfonyApiUrl;
    private $phpDocUrl;
    private $symfonyDocUrl;

    private $sourceDir;
    private $htmlOutputDir;
    private $jsonOutputDir;
    private $parseOnly;

    public function __construct(
        string $basePath,
        string $symfonyVersion,
        string $symfonyApiUrl,
        string $phpDocUrl,
        string $symfonyDocUrl
    ) {
        $this->basePath       = $basePath;
        $this->symfonyVersion = $symfonyVersion;
        $this->symfonyApiUrl  = $symfonyApiUrl;
        $this->phpDocUrl      = $phpDocUrl;
        $this->symfonyDocUrl  = $symfonyDocUrl;
    }

    public function initialize(string $sourceDir, string $htmlOutputDir, string $jsonOutputDir, string $parseOnly)
    {
        $this->sourceDir     = $sourceDir;
        $this->htmlOutputDir = $htmlOutputDir;
        $this->jsonOutputDir = $jsonOutputDir;
        $this->parseOnly     = $parseOnly;
    }

    public function getBasePath(): string
    {
        return $this->basePath;
    }

    public function getSymfonyVersion(): string
    {
        return $this->symfonyVersion;
    }

    public function getSymfonyApiUrl(): string
    {
        return $this->symfonyApiUrl;
    }

    public function getPhpDocUrl(): string
    {
        return $this->phpDocUrl;
    }

    public function getSymfonyDocUrl(): string
    {
        return $this->symfonyDocUrl;
    }

    public function getSourceDir(): ?string
    {
        return $this->sourceDir;
    }

    public function getHtmlOutputDir(): ?string
    {
        return $this->htmlOutputDir;
    }

    public function getJsonOutputDir(): ?string
    {
        return $this->jsonOutputDir;
    }

    public function getParseOnly(): ?string
    {
        return $this->parseOnly;
    }
}
