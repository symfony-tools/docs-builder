<?php

namespace SymfonyDocsBuilder\Reference;

use Doctrine\RST\Environment;
use Doctrine\RST\References\Reference;
use Doctrine\RST\References\ResolvedReference;

class PhpFunctionReference extends Reference
{
    private $phpDocUrl;

    public function __construct(string $phpDocUrl)
    {
        $this->phpDocUrl = $phpDocUrl;
    }

    public function getName(): string
    {
        return 'phpfunction';
    }

    public function resolve(Environment $environment, string $data): ResolvedReference
    {
        return new ResolvedReference(
            $data,
            sprintf('%s/function.%s.php', $this->phpDocUrl, str_replace('_', '-', strtolower($data))),
            [],
            [
                'title' => $data,
            ]
        );
    }
}