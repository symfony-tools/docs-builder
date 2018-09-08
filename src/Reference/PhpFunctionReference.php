<?php

namespace SymfonyDocs\Reference;

use Doctrine\RST\Environment;
use Doctrine\RST\Reference;

class PhpFunctionReference extends Reference
{
    public function getName(): string
    {
        return 'phpfunction';
    }

    public function resolve(Environment $environment, string $data): ?array
    {
        return ['url' => 'foo'];
    }

    public function resolveByText(Environment $environment, string $text): ?array
    {
        return [];
    }

}