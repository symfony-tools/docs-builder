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

use League\Flysystem\Adapter\Local;
use League\Flysystem\Filesystem as LeagueFilesystem;
use phpDocumentor\FileSystem\FileSystem;
use phpDocumentor\FileSystem\FlysystemV3\FlysystemV3;

final class LocalBuildEnvironment implements BuildEnvironment
{
    private string $sourceDir;
    private ?LeagueFilesystem $sourceFilesystem = null;
    private string $outputDir;
    private ?LeagueFilesystem $outputFilesystem = null;

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

    public function getSourceFilesystem(): FileSystem
    {
        return $this->sourceFilesystem ??= new FlysystemV3(new LeagueFilesystem(new Local($this->sourceDir)));
    }

    public function getOutputFilesystem(): FileSystem
    {
        return $this->outputFilesystem ??= new FlysystemV3(new LeagueFilesystem(new Local($this->outputDir)));
    }
}
