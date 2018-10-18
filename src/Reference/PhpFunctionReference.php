<?php

namespace SymfonyDocs\Reference;

use Doctrine\RST\Environment;
use Doctrine\RST\Reference;
use Doctrine\RST\References\ResolvedReference;
use SymfonyDocs\HtmlKernel;

class PhpFunctionReference extends Reference
{
    public function getName(): string
    {
        return 'phpfunction';
    }

    public function resolve(Environment $environment, string $data): ResolvedReference
    {
        return new ResolvedReference(
            $data,
            sprintf('%s/function.%s.php', HtmlKernel::getPhpDocUrl(), str_replace('_', '-', strtolower($data))),
            [],
            [
                'class' => 'reference external',
                'title' => $data,
            ]
        );
    }
}