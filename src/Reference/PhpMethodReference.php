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

class PhpMethodReference extends Reference
{
    private $phpDocUrl;

    public function __construct(string $phpDocUrl)
    {
        $this->phpDocUrl = $phpDocUrl;
    }

    public function getName(): string
    {
        return 'phpmethod';
    }

    public function resolve(Environment $environment, string $data): ResolvedReference
    {
        $data = u($data);
        if (!$data->containsAny('::')) {
            throw new \RuntimeException(sprintf('Malformed method reference "%s" in file "%s"', $data, $environment->getCurrentFileName()));
        }

        [$className, $methodName] = $data->split('::', 2);
        $className = $className->replace('\\\\', '\\');

        return new ResolvedReference(
            $environment->getCurrentFileName(),
            $methodName.'()',
            sprintf('%s/%s.%s.php', $this->phpDocUrl, $className->replace('\\', '-')->lower(), $methodName->lower()),
            [],
            [
                'title' => sprintf('%s::%s()', $className, $methodName),
            ]
        );
    }
}
