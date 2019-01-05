<?php

namespace SymfonyDocsBuilder;

class BuildContext
{
    private $basePath;
    private $symfonyVersion;
    private $symfonyApiUrl;
    private $phpDocUrl;
    private $symfonyDocUrl;

    private $runtimeInitialized = false;
    private $sourceDir;
    private $htmlOutputDir;
    private $jsonOutputDir;
    private $parseSubPath;

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

    public function initializeRuntimeConfig(string $sourceDir, string $htmlOutputDir, ?string $jsonOutputDir = null, ?string $parseSubPath = null)
    {
        $this->sourceDir          = $sourceDir;
        $this->htmlOutputDir      = $htmlOutputDir;
        $this->jsonOutputDir      = $jsonOutputDir;
        $this->parseSubPath       = $parseSubPath;
        $this->runtimeInitialized = true;
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

    public function getSourceDir(): string
    {
        $this->checkThatRuntimeConfigIsInitialized();

        return $this->sourceDir;
    }

    public function getHtmlOutputDir(): string
    {
        $this->checkThatRuntimeConfigIsInitialized();

        return $this->htmlOutputDir;
    }

    public function getJsonOutputDir(): string
    {
        $this->checkThatRuntimeConfigIsInitialized();

        return $this->jsonOutputDir;
    }

    public function getParseSubPath(): ?string
    {
        $this->checkThatRuntimeConfigIsInitialized();

        return $this->parseSubPath;
    }

    private function checkThatRuntimeConfigIsInitialized()
    {
        if (false === $this->runtimeInitialized) {
            throw new \LogicException('The BuildContext has not been initialized');
        }
    }
}
