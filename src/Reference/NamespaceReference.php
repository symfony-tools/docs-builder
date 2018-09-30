<?php

namespace SymfonyDocs\Reference;

use Doctrine\RST\Environment;
use Doctrine\RST\Reference;
use Doctrine\RST\References\ResolvedReference;

class NamespaceReference extends Reference
{
    private const BASE__URL = 'https://api.symfony.com';

    public function getName(): string
    {
        return 'namespace';
    }

    public function resolve(Environment $environment, string $data): ResolvedReference
    {
        $className = str_replace('\\\\', '\\', $data);

        return new ResolvedReference(
            substr(strrchr($className, '\\'), 1),
            sprintf('%s/%s/%s.html', self::BASE__URL, '4.1', str_replace('\\', '/', $className)),
            [],
            [
                'class' => 'reference external',
                'title' => $className
            ]
        );
    }
}