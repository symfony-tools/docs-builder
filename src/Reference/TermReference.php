<?php

namespace SymfonyDocsBuilder\Reference;

use Doctrine\RST\Environment;
use Doctrine\RST\References\Reference;
use Doctrine\RST\References\ResolvedReference;

class TermReference extends Reference
{
    public function getName(): string
    {
        return 'term';
    }

    public function resolve(Environment $environment, string $data): ResolvedReference
    {
        return new ResolvedReference(
            $environment->getCurrentFileName(),
            $data,
            '#'
        );
    }
}