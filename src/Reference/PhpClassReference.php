<?php

namespace SymfonyDocsBuilder\Reference;

use Doctrine\RST\Environment;
use Doctrine\RST\References\Reference;
use Doctrine\RST\References\ResolvedReference;

class PhpClassReference extends Reference
{
    private $phpDocUrl;

    public function __construct(string $phpDocUrl)
    {
        $this->phpDocUrl = $phpDocUrl;
    }

    public function getName(): string
    {
        return 'phpclass';
    }

    public function resolve(Environment $environment, string $data): ResolvedReference
    {
        return new ResolvedReference(
            $data,
            sprintf('%s/class.%s.php', $this->phpDocUrl, strtolower($data)),
            [],
            [
                'title' => $data,
            ]
        );
    }
}