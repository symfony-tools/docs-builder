<?php

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
