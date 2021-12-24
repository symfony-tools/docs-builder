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
    private $githubUrl;

    public function __construct(string $githubUrl)
    {
        $this->githubUrl = $githubUrl;
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

        return new ResolvedReference(
            $environment->getCurrentFileName(),
            $methodName.'()',
            sprintf('%s/%s.php#method_%s', $this->githubUrl, str_replace('\\', '/', $className), $methodName),
            [],
            [
                'title' => sprintf('%s::%s()', $className, $methodName),
            ]
        );
    }
}
