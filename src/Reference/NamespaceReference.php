<?php

namespace SymfonyDocsBuilder\Reference;

use Doctrine\RST\Environment;
use Doctrine\RST\References\Reference;
use Doctrine\RST\References\ResolvedReference;

class NamespaceReference extends Reference
{
    /** @var string */
    private $symfonyApiUrl;

    public function __construct(string $symfonyApiUrl)
    {
        $this->symfonyApiUrl = $symfonyApiUrl;
    }

    public function getName(): string
    {
        return 'namespace';
    }

    public function resolve(Environment $environment, string $data): ResolvedReference
    {
        $className = str_replace('\\\\', '\\', $data);

        return new ResolvedReference(
            substr(strrchr($className, '\\'), 1),
            sprintf('%s/%s.html', $this->symfonyApiUrl, str_replace('\\', '/', $className)),
            [],
            [
                'class' => 'reference external',
                'title' => $className,
            ]
        );
    }
}