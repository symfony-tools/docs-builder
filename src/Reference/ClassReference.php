<?php

namespace SymfonyDocs\Reference;

use Doctrine\RST\Environment;
use Doctrine\RST\References\Reference;
use Doctrine\RST\References\ResolvedReference;
use SymfonyDocs\SymfonyDocConfiguration;

class ClassReference extends Reference
{
    public function getName(): string
    {
        return 'class';
    }

    public function resolve(Environment $environment, string $data): ResolvedReference
    {
        $className = str_replace('\\\\', '\\', $data);

        return new ResolvedReference(
            substr(strrchr($className, '\\'), 1),
            sprintf('%s/%s/%s.html', SymfonyDocConfiguration::getSymfonyApiUrl(), SymfonyDocConfiguration::getVersion(), str_replace('\\', '/', $className)),
            [],
            [
                'class' => 'reference external',
                'title' => $className,
            ]
        );
    }
}
