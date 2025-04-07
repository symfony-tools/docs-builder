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

class PhpClassReference extends Reference
{
    private $phpDocUrl;

    public function __construct(string $phpDocUrl)
    {
        $this->phpDocUrl = $phpDocUrl;
    }

    public function getName(): string
    {
        return 'phpclass';
    }

    public function resolve(Environment $environment, string $data): ResolvedReference
    {
        $className = u($data)->replace('\\\\', '\\');

        return new ResolvedReference(
            $environment->getCurrentFileName(),
            $className->afterLast('\\'),
            sprintf('%s/class.%s.php', $this->phpDocUrl, $className->replace('\\', '-')->lower()),
            [],
            [
                'title' => $className,
            ]
        );
    }
}
