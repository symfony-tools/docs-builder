<?php

namespace SymfonyDocs\Reference;

use Doctrine\RST\Environment;
use Doctrine\RST\References\Reference;
use Doctrine\RST\References\ResolvedReference;
use SymfonyDocs\SymfonyDocConfiguration;

class PhpClassReference extends Reference
{
    public function getName(): string
    {
        return 'phpclass';
    }

    public function resolve(Environment $environment, string $data): ResolvedReference
    {
        return new ResolvedReference(
            $data,
            sprintf('%s/class.%s.php', SymfonyDocConfiguration::getPhpDocUrl(), strtolower($data)),
            [],
            [
                'class' => 'reference external',
                'title' => $data,
            ]
        );
    }
}