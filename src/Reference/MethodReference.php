<?php

namespace SymfonyDocsBuilder\Reference;

use Doctrine\RST\Environment;
use Doctrine\RST\References\Reference;
use Doctrine\RST\References\ResolvedReference;

class MethodReference extends Reference
{
    private $symfonyApiUrl;

    public function __construct(string $symfonyApiUrl)
    {
        $this->symfonyApiUrl = $symfonyApiUrl;
    }

    public function getName(): string
    {
        return 'method';
    }

    public function resolve(Environment $environment, string $data): ResolvedReference
    {
        $className = explode('::', $data)[0];
        $className = str_replace('\\\\', '\\', $className);

        $methodName = explode('::', $data)[1];

        return new ResolvedReference(
            $methodName.'()',
            sprintf('%s/%s.html#method_%s', $this->symfonyApiUrl, str_replace('\\', '/', $className), $methodName),
            [],
            [
                'class' => 'reference external',
                'title' => sprintf('%s::%s()', $className, $methodName),
            ]
        );
    }
}