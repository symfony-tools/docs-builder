<?php

/*
 * This file is part of the Guides SymfonyExtension package.
 *
 * (c) Wouter de Jong
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SymfonyTools\GuidesExtension\TextRole;

use phpDocumentor\Guides\Nodes\Inline\InlineNode;
use phpDocumentor\Guides\RestructuredText\Parser\DocumentParserContext;
use phpDocumentor\Guides\RestructuredText\TextRoles\TextRole;
use SymfonyTools\GuidesExtension\Build\BuildConfig;
use SymfonyTools\GuidesExtension\Node\ExternalLinkNode;

class PhpFunctionRole implements TextRole
{
    public function __construct(
        private BuildConfig $buildConfig
    ) {
    }

    public function processNode(DocumentParserContext $documentParserContext, string $role, string $content, string $rawContent): InlineNode
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
