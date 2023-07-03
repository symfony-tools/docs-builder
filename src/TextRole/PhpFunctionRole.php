<?php

namespace SymfonyTools\GuidesExtension\TextRole;

use SymfonyTools\GuidesExtension\Build\BuildConfig;
use SymfonyTools\GuidesExtension\Node\ExternalLinkNode;
use phpDocumentor\Guides\Nodes\Inline\InlineNode;
use phpDocumentor\Guides\ParserContext;
use phpDocumentor\Guides\RestructuredText\TextRoles\TextRole;

class PhpFunctionRole implements TextRole
{
    public function __construct(
        private BuildConfig $buildConfig
    ) {
    }

    public function processNode(ParserContext $parserContext, string $role, string $content, string $rawContent): InlineNode
    {
        $url = 'https://php.net/function.'.strtolower(str_replace('_', '-', $content));
        $content .= '()';

        return new ExternalLinkNode($url, $content, $content);
    }

    public function getName(): string
    {
        return 'phpfunction';
    }

    public function getAliases(): array
    {
        return [];
    }
}
