<?php

namespace SymfonyDocs\Reference;

use Doctrine\RST\Environment;
use Doctrine\RST\Reference;
use Doctrine\RST\References\ResolvedReference;

class PhpFunctionReference extends Reference
{
    private const BASE__URL = 'https://secure.php.net/manual/en/function.%s.php';

    public function getName(): string
    {
        return 'phpfunction';
    }

    public function resolve(Environment $environment, string $data): ResolvedReference
    {
        return new ResolvedReference(
            $data,
            sprintf(self::BASE__URL, str_replace('_', '-', strtolower($data)))
        );
    }
}