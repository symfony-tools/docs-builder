<?php

/*
 * This file is part of the Docs Builder package.
 * (c) Ryan Weaver <ryan@symfonycasts.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SymfonyDocsBuilder\Reference;

use Doctrine\RST\Environment;
use Doctrine\RST\References\Reference;
use Doctrine\RST\References\ResolvedReference;
use function Symfony\Component\String\u;

class ClassReference extends Reference
{
    private $symfonyRepositoryUrl;

    public function __construct(string $symfonyRepositoryUrl)
    {
        $this->symfonyRepositoryUrl = $symfonyRepositoryUrl;
    }

    public function getName(): string
    {
        return 'class';
    }

    public function resolve(Environment $environment, string $data): ResolvedReference
    {
        $className = u($data)->replace('\\\\', '\\');

        return new ResolvedReference(
            $environment->getCurrentFileName(),
            $className->afterLast('\\'),
            sprintf('%s/%s.php', $this->symfonyRepositoryUrl, $className->replace('\\', '/')),
            [],
            [
                'title' => $className,
            ]
        );
    }
}
