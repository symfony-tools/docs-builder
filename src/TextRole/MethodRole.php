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

use function Symfony\Component\String\u;

class MethodRole implements TextRole
{
    public function __construct(
        private BuildConfig $buildConfig
    ) {
    }

    public function processNode(DocumentParserContext $documentParserContext, string $role, string $content, string $rawContent): InlineNode
    {
        [$fqcn, $method] = u($content)->replace('\\\\', '\\')->split('::', 2);

        $filename = sprintf('%s.php#:~:text=%s', $fqcn->replace('\\', '/'), rawurlencode('function '.$method));
        $url = sprintf($this->buildConfig->getSymfonyRepositoryUrl(), $filename);

        return new ExternalLinkNode($url, $method.'()', $fqcn.'::'.$method.'()');
    }

    public function getName(): string
    {
        return 'method';
    }

    public function getAliases(): array
    {
        return [];
    }
}
