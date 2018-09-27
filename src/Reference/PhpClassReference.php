<?php

namespace SymfonyDocs\Reference;

use Doctrine\RST\Environment;
use Doctrine\RST\Reference;
use Doctrine\RST\References\Resolver;
use Doctrine\RST\References\ResolvedReference;

class PhpClassReference extends Reference
{
    public function getName(): string
    {
        return 'phpclass';
    }

    public function resolve(Environment $environment, string $data): ResolvedReference
    {
        $resolver = new Resolver();
        return $resolver->resolve($environment, $data);
    }
}