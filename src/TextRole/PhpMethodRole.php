<?php

namespace SymfonyTools\GuidesExtension\TextRole;

use SymfonyTools\GuidesExtension\Build\BuildConfig;
use SymfonyTools\GuidesExtension\Node\ExternalLinkNode;
use phpDocumentor\Guides\Nodes\Inline\InlineNode;
use phpDocumentor\Guides\ParserContext;
use phpDocumentor\Guides\RestructuredText\TextRoles\TextRole;

class PhpMethodRole implements TextRole
{
    public function __construct(
        private BuildConfig $buildConfig
    ) {
    }

    public function processNode(ParserContext $parserContext, string $role, string $content, string $rawContent): InlineNode
    {
        [$fqcn, $method] = explode('::', $content, 2);

        $url = 'https://php.net/'.strtolower($fqcn).'.'.strtolower($method);
        $content .= '()';

        return new ExternalLinkNode($url, $content, $content);
    }

    public function getName(): string
    {
        return 'phpmethod';
    }

    public function getAliases(): array
    {
        return [];
    }
}
