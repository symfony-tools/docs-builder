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
use League\Flysystem\AdapterInterface;
use League\Flysystem\Filesystem;
use League\Flysystem\Memory\MemoryAdapter;

final class DynamicBuildEnvironment implements BuildEnvironment
{
    private Filesystem $sourceFilesystem;
    private Filesystem $outputFilesystem;

    public function __construct(AdapterInterface $sourceAdapter = null, AdapterInterface $outputAdapter = null)
    {
        $this->sourceFilesystem = (new Filesystem($sourceAdapter ?? new MemoryAdapter()))->addPlugin(new Finder());
        $this->outputFilesystem = (new Filesystem($outputAdapter ?? new MemoryAdapter()))->addPlugin(new Finder());
    }

    public function getSourceFilesystem(): Filesystem
    {
        return $this->sourceFilesystem;
    }

    public function getOutputFilesystem(): Filesystem
    {
        return $this->outputFilesystem;
    }
}
