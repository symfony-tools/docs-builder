<?php

namespace SymfonyDocs\Reference;

use Doctrine\RST\Environment;
use Doctrine\RST\Reference;
use Doctrine\RST\References\Resolver;
use Doctrine\RST\References\ResolvedReference;

class RefReference extends Reference
{
    public function getName(): string
    {
        return 'ref';
    }

    public function resolve(Environment $environment, string $data): ResolvedReference
    {
        $resolver = new Resolver();
        dump($resolver->resolve($environment, $data));
        return $resolver->resolve($environment, $data);
    }
}