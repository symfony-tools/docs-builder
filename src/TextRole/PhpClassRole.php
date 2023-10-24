<?php

namespace SymfonyTools\GuidesExtension\TextRole;

use SymfonyTools\GuidesExtension\Build\BuildConfig;
use SymfonyTools\GuidesExtension\Node\ExternalLinkNode;
use phpDocumentor\Guides\Nodes\Inline\InlineNode;
use phpDocumentor\Guides\RestructuredText\Parser\DocumentParserContext;
use phpDocumentor\Guides\RestructuredText\TextRoles\TextRole;

class PhpClassRole implements TextRole
{
    public function __construct(
        private BuildConfig $buildConfig
    ) {
    }

    public function processNode(DocumentParserContext $parserContext, string $role, string $content, string $rawContent): InlineNode
    {
        $url = 'https://php.net/class.'.strtolower($content);

        return new ExternalLinkNode($url, $content, $content);
    }

    public function getName(): string
    {
        return 'phpclass';
    }

    public function getAliases(): array
    {
        return [];
    }
}
