<?php

namespace SymfonyTools\GuidesExtension\TextRole;

use SymfonyTools\GuidesExtension\Build\BuildConfig;
use SymfonyTools\GuidesExtension\Node\ExternalLinkNode;
use phpDocumentor\Guides\Nodes\Inline\InlineNode;
use phpDocumentor\Guides\RestructuredText\Parser\DocumentParserContext;
use phpDocumentor\Guides\RestructuredText\TextRoles\TextRole;
use function Symfony\Component\String\u;

class NamespaceRole implements TextRole
{
    public function __construct(
        private BuildConfig $buildConfig
    ) {
    }

    public function processNode(DocumentParserContext $parserContext, string $role, string $content, string $rawContent): InlineNode
    {
        $fqcn = u($content)->replace('\\\\', '\\');

        $url = sprintf($this->buildConfig->getSymfonyRepositoryUrl(), $fqcn->replace('\\', '/'));

        return new ExternalLinkNode($url, $fqcn->afterLast('\\'), $fqcn);
    }

    public function getName(): string
    {
        return 'namespace';
    }

    public function getAliases(): array
    {
        return [];
    }
}
