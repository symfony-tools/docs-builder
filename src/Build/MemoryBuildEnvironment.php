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
use League\Flysystem\Filesystem;
use League\Flysystem\Memory\MemoryAdapter;

final class MemoryBuildEnvironment implements BuildEnvironment
{
    private ?Filesystem $filesystem = null;

    public function getSourceFilesystem(): Filesystem
    {
        return $this->getFilesystem();
    }

    public function getOutputFilesystem(): Filesystem
    {
        return $this->getFilesystem();
    }

    private function getFilesystem(): Filesystem
    {
        return $this->filesystem ??= (new Filesystem(new MemoryAdapter()))->addPlugin(new Finder());
    }
}
