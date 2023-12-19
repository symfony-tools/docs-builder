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

use League\Flysystem\Filesystem;

interface BuildEnvironment
{
    public function getSourceFilesystem(): Filesystem;

    public function getOutputFilesystem(): Filesystem;
}
