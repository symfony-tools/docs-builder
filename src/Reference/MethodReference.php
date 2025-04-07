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
        $data = u($data);
        if (!$data->containsAny('::')) {
            throw new \RuntimeException(sprintf('Malformed method reference "%s" in file "%s"', $data, $environment->getCurrentFileName()));
        }

        [$className, $methodName] = $data->split('::', 2);
        $className = $className->replace('\\\\', '\\');

        $scrollTextFragment = sprintf('#:~:text=%s', rawurlencode('function '.$methodName));
        return new ResolvedReference(
            $environment->getCurrentFileName(),
            $methodName.'()',
            sprintf('%s/%s.php%s', $this->symfonyRepositoryUrl, $className->replace('\\', '/'), $scrollTextFragment),
            [],
            [
                'title' => sprintf('%s::%s()', $className, $methodName),
            ]
        );
    }
}
