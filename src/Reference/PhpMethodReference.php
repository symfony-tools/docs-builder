<?php

namespace SymfonyDocs\Reference;

use Doctrine\RST\Environment;
use Doctrine\RST\Reference;
use Doctrine\RST\References\ResolvedReference;

class PhpMethodReference extends Reference
{
    private const BASE__URL = 'https://secure.php.net/manual/en/%s.%s.php';

    public function getName(): string
    {
        return 'phpmethod';
    }

    public function resolve(Environment $environment, string $data): ResolvedReference
    {
        $class = explode('::', $data)[0];
        $method = explode('::', $data)[1];

        return new ResolvedReference(
            $data.'()',
            sprintf(self::BASE__URL, strtolower($class), strtolower($method))
        );
    }
}