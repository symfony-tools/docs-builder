<?php

/*
 * This file is part of the Guides SymfonyExtension package.
 *
 * (c) Wouter de Jong
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SymfonyDocsBuilder\TextRole;

use phpDocumentor\Guides\Nodes\Inline\InlineNodeInterface;
use phpDocumentor\Guides\RestructuredText\Parser\DocumentParserContext;
use phpDocumentor\Guides\RestructuredText\TextRoles\TextRole;
use SymfonyDocsBuilder\Build\BuildConfig;
use SymfonyDocsBuilder\Node\ExternalLinkNode;

final class PhpFunctionRole implements TextRole
{
    public function __construct(
        private BuildConfig $buildConfig,
    ) {
    }

    #[\Override]
    public function processNode(DocumentParserContext $documentParserContext, string $role, string $content, string $rawContent): InlineNodeInterface
    {
        $url = 'https://php.net/function.'.strtolower(str_replace('_', '-', $content));
        $content .= '()';

        return new ExternalLinkNode($url, $content, $content);
    }

    #[\Override]
    public function getName(): string
    {
        return 'phpfunction';
    }

    #[\Override]
    public function getAliases(): array
    {
        return [];
    }
}
