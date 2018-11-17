<?php

namespace SymfonyDocs\Reference;

use Doctrine\RST\Environment;
use Doctrine\RST\References\Reference;
use Doctrine\RST\References\ResolvedReference;
use SymfonyDocs\SymfonyDocConfiguration;

class PhpFunctionReference extends Reference
{
    public function getName(): string
    {
        return 'phpfunction';
    }

    public function resolve(Environment $environment, string $data): ResolvedReference
    {
        return new ResolvedReference(
            $data,
            sprintf('%s/function.%s.php', SymfonyDocConfiguration::getPhpDocUrl(), str_replace('_', '-', strtolower($data))),
            [],
            [
                'class' => 'reference external',
                'title' => $data,
            ]
        );
    }
}