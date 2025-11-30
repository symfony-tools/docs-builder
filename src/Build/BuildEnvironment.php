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

use phpDocumentor\FileSystem\FileSystem;

interface BuildEnvironment
{
    public function getSourceFilesystem(): FileSystem;

    public function getOutputFilesystem(): FileSystem;
}
