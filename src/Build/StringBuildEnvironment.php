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
use League\Flysystem\InMemory\InMemoryFilesystemAdapter;
use phpDocumentor\FileSystem\FileSystem;
use phpDocumentor\FileSystem\FlysystemV3\FlysystemV3;

final class StringBuildEnvironment implements BuildEnvironment
{
    private FileSystem $filesystem;

    public function __construct(string $contents)
    {
        $this->filesystem = new FlysystemV3(new LeagueFilesystem(new InMemoryFilesystemAdapter()));
        $this->filesystem->put('index.rst', $contents);
    }

    public function getSourceFilesystem(): FileSystem
    {
        return $this->filesystem;
    }

    public function getOutputFilesystem(): FileSystem
    {
        return $this->filesystem;
    }

    public function getOutput(): ?string
    {
        $output = $this->filesystem->read('/index.html');

        return $output ?: null;
  }
}
