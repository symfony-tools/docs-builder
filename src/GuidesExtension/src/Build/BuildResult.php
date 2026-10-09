<?php

/*
 * This file is part of the Guides SymfonyExtension package.
 *
 * (c) Wouter de Jong
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SymfonyTools\DocsBuilder\GuidesExtension\Build;

final class BuildResult
{
    public function __construct(
        public readonly bool $success,
        public readonly string $errorTrace,
    ) {
    }
}
