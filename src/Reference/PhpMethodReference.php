<?php

namespace SymfonyDocs\Reference;

use Doctrine\RST\Environment;
use Doctrine\RST\Reference;
use Doctrine\RST\References\ResolvedReference;
use SymfonyDocs\HtmlKernel;

class PhpMethodReference extends Reference
{
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
            sprintf('%s/%s.%s.php', HtmlKernel::getPhpDocUrl(), strtolower($class), strtolower($method)),
            [],
            [
                'class' => 'reference external',
                'title' => $class,
            ]
        );
    }
}