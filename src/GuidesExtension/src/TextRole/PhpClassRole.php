<?php

/*
 * This file is part of the Guides SymfonyExtension package.
 *
 * (c) Wouter de Jong
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SymfonyTools\DocsBuilder\GuidesExtension\TextRole;

use phpDocumentor\Guides\Nodes\Inline\InlineNodeInterface;
use phpDocumentor\Guides\RestructuredText\Parser\DocumentParserContext;
use phpDocumentor\Guides\RestructuredText\TextRoles\TextRole;
use SymfonyTools\DocsBuilder\GuidesExtension\Build\BuildConfig;
use SymfonyTools\DocsBuilder\GuidesExtension\Node\ExternalLinkNode;

use function Symfony\Component\String\u;

final class PhpClassRole implements TextRole
{
    public function __construct(
        private BuildConfig $buildConfig,
    ) {
    }

    public function processNode(DocumentParserContext $documentParserContext, string $role, string $content, string $rawContent): InlineNodeInterface
    {
        $fqcn = u($content);
        $url = 'https://php.net/class.'.$fqcn->lower()->replace('\\', '-');

        return new ExternalLinkNode($url, $fqcn->afterLast('\\'), $content);
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
