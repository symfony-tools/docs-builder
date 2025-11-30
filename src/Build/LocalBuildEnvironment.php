<?php

/*
 * This file is part of the Guides SymfonyExtension package.
 *
 * (c) Wouter de Jong
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SymfonyDocsBuilder\Build;

use League\Flysystem\Filesystem as LeagueFilesystem;
use League\Flysystem\Local\LocalFilesystemAdapter;
use phpDocumentor\FileSystem\FileSystem;
use phpDocumentor\FileSystem\FlysystemV3\FlysystemV3;

final class LocalBuildEnvironment implements BuildEnvironment
{
    private ?string $sourceDir = null;
    private ?FileSystem $sourceFilesystem = null;
    private ?string $outputDir = null;
    private ?FileSystem $outputFilesystem = null;

    public function __construct()
    {
        if ($cwd = getcwd()) {
            $this->setSourceDir($cwd);
        }
    }

    public function setSourceDir(string $sourceDir): void
    {
        if ($sourceDir === $this->sourceDir) {
            return;
        }

        $this->sourceDir = $sourceDir;
        $this->sourceFilesystem = null;

        if (null == $this->outputDir) {
            $this->setOutputDir($sourceDir.'/_output');
        }
    }

    public function setOutputDir(string $outputDir): void
    {
        if ($outputDir !== $this->outputDir) {
            $this->outputFilesystem = null;
        }
        $this->outputDir = $outputDir;
    }

    #[\Override]
    public function getSourceFilesystem(): FileSystem
    {
        if (null === $this->sourceDir) {
            throw new \BadMethodCallException('Cannot get source filesystem: no source directory set.');
        }

        return $this->sourceFilesystem ??= new FlysystemV3(new LeagueFilesystem(new LocalFilesystemAdapter($this->sourceDir)));
    }

    #[\Override]
    public function getOutputFilesystem(): FileSystem
    {
        if (null === $this->outputDir) {
            throw new \BadMethodCallException('Cannot get output filesystem: no output directory set.');
        }

        return $this->outputFilesystem ??= new FlysystemV3(new LeagueFilesystem(new LocalFilesystemAdapter($this->outputDir)));
    }
}
