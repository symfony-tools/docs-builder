<?php

namespace SymfonyDocsBuilder;

class BuildContext
{
    private $symfonyVersion;
    private $symfonyApiUrl;
    private $phpDocUrl;
    private $symfonyDocUrl;

    private $runtimeInitialized = false;
    private $sourceDir;
    private $outputDir;
    private $parseSubPath;
    private $disableCache = false;
    private $cacheDirectory;

    public function __construct(
        string $symfonyVersion,
        string $symfonyApiUrl,
        string $phpDocUrl,
        string $symfonyDocUrl
    ) {
        $this->symfonyVersion = $symfonyVersion;
        $this->symfonyApiUrl  = $symfonyApiUrl;
        $this->phpDocUrl      = $phpDocUrl;
        $this->symfonyDocUrl  = $symfonyDocUrl;
    }

    public function initializeRuntimeConfig(string $sourceDir, string $outputDir, ?string $parseSubPath = null, bool $disableCache = false)
    {
        $this->sourceDir          = $sourceDir;
        $this->outputDir          = $outputDir;
        $this->parseSubPath       = $parseSubPath;
        $this->disableCache       = $disableCache;
        $this->runtimeInitialized = true;
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

    public function getOutputDir(): string
    {
        $this->checkThatRuntimeConfigIsInitialized();

        return $this->outputDir;
    }

    public function getParseSubPath(): ?string
    {
        $this->checkThatRuntimeConfigIsInitialized();

        return $this->parseSubPath;
    }

    public function getDisableCache(): bool
    {
        $this->checkThatRuntimeConfigIsInitialized();

        return $this->disableCache;
    }

    public function getCacheDir(): string
    {
        return $this->cacheDirectory ?: $this->getOutputDir().'/.cache';
    }

    public function setCacheDirectory(string $cacheDirectory)
    {
        $this->cacheDirectory = $cacheDirectory;
    }

    private function checkThatRuntimeConfigIsInitialized()
    {
        if (false === $this->runtimeInitialized) {
            throw new \LogicException('The BuildContext has not been initialized');
        }
    }
}
