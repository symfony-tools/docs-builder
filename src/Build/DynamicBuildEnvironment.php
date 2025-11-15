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

use League\Flysystem\Filesystem as LeagueFilesystem;
use League\Flysystem\FilesystemAdapter;
use League\Flysystem\InMemory\InMemoryFilesystemAdapter;
use phpDocumentor\FileSystem\FileSystem;
use phpDocumentor\FileSystem\FlysystemV3\FlysystemV3;

final class DynamicBuildEnvironment implements BuildEnvironment
{
    private FileSystem $sourceFilesystem;
    private FileSystem $outputFilesystem;

    public function __construct(?FilesystemAdapter $sourceAdapter = null, ?FilesystemAdapter $outputAdapter = null)
    {
        $this->sourceFilesystem = new FlysystemV3(new LeagueFilesystem($sourceAdapter ?? new InMemoryFilesystemAdapter()));
        $this->outputFilesystem = new FlysystemV3(new LeagueFilesystem($outputAdapter ?? new InMemoryFilesystemAdapter()));
    }

    #[\Override]
    public function getSourceFilesystem(): FileSystem
    {
        return $this->sourceFilesystem;
    }

    #[\Override]
    public function getOutputFilesystem(): FileSystem
    {
        return $this->outputFilesystem;
    }
}
