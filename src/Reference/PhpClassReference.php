<?php

namespace SymfonyDocs\Reference;

use Doctrine\RST\Environment;
use Doctrine\RST\Reference;
use Doctrine\RST\References\ResolvedReference;

class PhpClassReference extends Reference
{
    private const BASE__URL = 'https://secure.php.net/manual/en/class.%s.php';

    public function getName(): string
    {
        return 'phpclass';
    }

    public function resolve(Environment $environment, string $data): ResolvedReference
    {
        return new ResolvedReference(
            $data,
            sprintf(self::BASE__URL, strtolower($data))
        );
    }
}