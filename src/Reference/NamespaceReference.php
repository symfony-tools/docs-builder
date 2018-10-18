<?php

namespace SymfonyDocs\Reference;

use Doctrine\RST\Environment;
use Doctrine\RST\Reference;
use Doctrine\RST\References\ResolvedReference;
use SymfonyDocs\HtmlKernel;

class NamespaceReference extends Reference
{
    public function getName(): string
    {
        return 'namespace';
    }

    public function resolve(Environment $environment, string $data): ResolvedReference
    {
        $className = str_replace('\\\\', '\\', $data);

        return new ResolvedReference(
            substr(strrchr($className, '\\'), 1),
            sprintf('%s/%s/%s.html', HtmlKernel::getSymfonyApiUrl(), HtmlKernel::getVersion(), str_replace('\\', '/', $className)),
            [],
            [
                'class' => 'reference external',
                'title' => $className
            ]
        );
    }
}