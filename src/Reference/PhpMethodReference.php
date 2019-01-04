<?php

namespace SymfonyDocsBuilder\Reference;

use Doctrine\RST\Environment;
use Doctrine\RST\References\Reference;
use Doctrine\RST\References\ResolvedReference;

class PhpMethodReference extends Reference
{
    private $phpDocUrl;

    public function __construct(string $phpDocUrl)
    {
        $this->phpDocUrl = $phpDocUrl;
    }

    public function getName(): string
    {
        return 'phpmethod';
    }

    public function resolve(Environment $environment, string $data): ResolvedReference
    {
        $class  = explode('::', $data)[0];
        $method = explode('::', $data)[1];

        return new ResolvedReference(
            $data.'()',
            sprintf('%s/%s.%s.php', $this->phpDocUrl, strtolower($class), strtolower($method)),
            [],
            [
                'class' => 'reference external',
                'title' => $class,
            ]
        );
    }
}