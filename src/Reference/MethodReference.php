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

class MethodReference extends Reference
{
    private $symfonyRepositoryUrl;

    public function __construct(string $symfonyRepositoryUrl)
    {
        $this->symfonyRepositoryUrl = $symfonyRepositoryUrl;
    }

    public function getName(): string
    {
        return 'method';
    }

    public function resolve(Environment $environment, string $data): ResolvedReference
    {
        $className = explode('::', $data)[0];
        $className = str_replace('\\\\', '\\', $className);

        if (!u($data)->containsAny('::')) {
            throw new \RuntimeException(sprintf('Malformed method reference "%s" in file "%s"', $data, $environment->getCurrentFileName()));
        }

        $methodName = explode('::', $data)[1];

        $scrollTextFragment = sprintf('#:~:text=%s', rawurlencode('function '.$methodName));
        return new ResolvedReference(
            $environment->getCurrentFileName(),
            $methodName.'()',
            sprintf('%s/%s.php%s', $this->symfonyRepositoryUrl, str_replace('\\', '/', $className), $scrollTextFragment),
            [],
            [
                'title' => sprintf('%s::%s()', $className, $methodName),
            ]
        );
    }
}
