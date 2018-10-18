<?php

namespace SymfonyDocs\Reference;

use Doctrine\RST\Environment;
use Doctrine\RST\Reference;
use Doctrine\RST\References\ResolvedReference;
use SymfonyDocs\HtmlKernel;

class MethodReference extends Reference
{
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
            sprintf('%s/%s/%s.html#method_%s', HtmlKernel::getSymfonyApiUrl(), HtmlKernel::getVersion(), str_replace('\\', '/', $className), $methodName),
            [],
            [
                'class' => 'reference external',
                'title' => sprintf('%s::%s()', $className, $methodName),
            ]
        );
    }
}