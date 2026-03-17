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

use phpDocumentor\Guides\Nodes\ProjectNode;

final class BuildConfig
{
    private const SYMFONY_REPOSITORY_URL = 'https://github.com/symfony/symfony/blob/{symfonyVersion}/src/%s';

    public string $outputFormat = 'html';

    public string $format {
        get => 'fjson' === $this->outputFormat ? 'html' : $this->outputFormat;
    }

    public string $symfonyRepositoryUrl {
        get => str_replace('{symfonyVersion}', $this->symfonyVersion, self::SYMFONY_REPOSITORY_URL);
    }

    public function __construct(
        public string $symfonyVersion = '6.1',
    ) {
    }

    public function createProjectNode(): ProjectNode
    {
        return new ProjectNode('Symfony', $this->symfonyVersion);
    }
}
