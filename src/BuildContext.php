<?php

/*
 * This file is part of the Docs Builder package.
 * (c) Ryan Weaver <ryan@symfonycasts.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SymfonyDocsBuilder;

use Doctrine\RST\Configuration;
use Symfony\Component\Finder\Finder;

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
    private $theme;
    private $cacheDirectory;
    private $excludedPaths = [];
    private $fileFinder;

    public function __construct(
        string $symfonyVersion,
        string $symfonyApiUrl,
        string $phpDocUrl,
        string $symfonyDocUrl
    ) {
        $this->symfonyVersion = $symfonyVersion;
        $this->symfonyApiUrl = $symfonyApiUrl;
        $this->phpDocUrl = $phpDocUrl;
        $this->symfonyDocUrl = $symfonyDocUrl;
    }

    public function initializeRuntimeConfig(string $sourceDir, string $outputDir, string $publicImagesDir, string $publicImagesPrefix, ?string $parseSubPath = null, bool $disableCache = false, string $theme = Configuration::THEME_DEFAULT)
    {
        if (!file_exists($sourceDir)) {
            throw new \Exception(sprintf('Source directory "%s" does not exist', $sourceDir));
        }

        if (!file_exists($outputDir)) {
            throw new \Exception(sprintf('Output directory "%s" does not exist', $outputDir));
        }

        if (!file_exists($publicImagesDir)) {
            throw new \Exception(sprintf('Public images directory "%s" does not exist', $publicImagesDir));
        }

        $this->sourceDir = realpath($sourceDir);
        $this->outputDir = realpath($outputDir);
        $this->publicImagesDir = realpath($publicImagesDir);
        $this->publicImagesPrefix = $publicImagesPrefix;
        $this->parseSubPath = $parseSubPath;
        $this->disableCache = $disableCache;
        $this->theme = $theme;
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

    public function getPublicImagesDir(): string
    {
        $this->checkThatRuntimeConfigIsInitialized();

        return $this->publicImagesDir;
    }

    public function getPublicImagesPrefix(): string
    {
        $this->checkThatRuntimeConfigIsInitialized();

        return $this->publicImagesPrefix;
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

    public function getTheme(): string
    {
        $this->checkThatRuntimeConfigIsInitialized();

        return $this->theme;
    }

    public function getCacheDir(): string
    {
        return $this->cacheDirectory ?: $this->getOutputDir().'/.cache';
    }

    public function setCacheDirectory(string $cacheDirectory)
    {
        $this->cacheDirectory = $cacheDirectory;
    }

    public function setExcludedPaths(array $excludedPaths)
    {
        if (null !== $this->fileFinder) {
            throw new \LogicException('setExcludePaths() cannot be called after getFileFinder() (because the Finder has been initialized).');
        }

        $this->excludedPaths = $excludedPaths;
    }

    public function createFileFinder(): Finder
    {
        if (null === $this->fileFinder) {
            $this->fileFinder = new Finder();
            $this->fileFinder
                ->in($this->getSourceDir())
                // TODO - read this from the rst-parser Configuration
                ->name('*.rst')
                ->notName('*.rst.inc')
                ->files()
                ->exclude($this->excludedPaths);
        }

        // clone to get a fresh instance and not share state
        return clone $this->fileFinder;
    }

    private function checkThatRuntimeConfigIsInitialized()
    {
        if (false === $this->runtimeInitialized) {
            throw new \LogicException('The BuildContext has not been initialized');
        }
    }
}
