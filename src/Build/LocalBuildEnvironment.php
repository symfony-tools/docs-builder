<?php

/*
 * This file is part of the Guides SymfonyExtension package.
 *
 * (c) Wouter de Jong
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SymfonyTools\GuidesExtension\Build;

use Flyfinder\Finder;
use League\Flysystem\Adapter\Local;
use League\Flysystem\Filesystem;

final class LocalBuildEnvironment implements BuildEnvironment
{
    private string $sourceDir;
    private ?Filesystem $sourceFilesystem = null;
    private string $outputDir;
    private ?Filesystem $outputFilesystem = null;

    public function __construct()
    {
        $this->sourceDir = getcwd();
        $this->outputDir = $this->sourceDir.'/_output';
    }

    public function setSourceDir(string $sourceDir): void
    {
        if ($sourceDir !== $this->sourceDir) {
            $this->sourceFilesystem = null;
        }
        $this->sourceDir = $sourceDir;
    }

    public function setOutputDir(string $outputDir): void
    {
        if ($outputDir !== $this->outputDir) {
            $this->outputFilesystem = null;
        }
        $this->outputDir = $outputDir;
    }

    public function getSourceFilesystem(): Filesystem
    {
        return $this->sourceFilesystem ??= (new Filesystem(new Local($this->sourceDir)))->addPlugin(new Finder());
    }

    public function getOutputFilesystem(): Filesystem
    {
        return $this->outputFilesystem ??= new Filesystem(new Local($this->outputDir));
    }
}
