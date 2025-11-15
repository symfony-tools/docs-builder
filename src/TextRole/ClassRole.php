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

use phpDocumentor\Guides\Nodes\Inline\InlineNodeInterface;
use phpDocumentor\Guides\RestructuredText\Parser\DocumentParserContext;
use phpDocumentor\Guides\RestructuredText\TextRoles\TextRole;
use SymfonyTools\GuidesExtension\Build\BuildConfig;
use SymfonyTools\GuidesExtension\Node\ExternalLinkNode;

use function Symfony\Component\String\u;

final class ClassRole implements TextRole
{
    public function __construct(
        private BuildConfig $buildConfig,
    ) {
    }

    #[\Override]
    public function processNode(DocumentParserContext $documentParserContext, string $role, string $content, string $rawContent): InlineNodeInterface
    {
        $fqcn = u($content)->replace('\\\\', '\\');

        $url = \sprintf($this->buildConfig->getSymfonyRepositoryUrl(), $fqcn->replace('\\', '/').'.php');

        return new ExternalLinkNode($url, (string) $fqcn->afterLast('\\'), (string) $fqcn);
    }

    #[\Override]
    public function getName(): string
    {
        return 'class';
    }

    #[\Override]
    public function getAliases(): array
    {
        return [];
    }
}
