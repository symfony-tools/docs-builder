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

final class MethodRole implements TextRole
{
    public function __construct(
        private BuildConfig $buildConfig,
    ) {
    }

    #[\Override]
    public function processNode(DocumentParserContext $documentParserContext, string $role, string $content, string $rawContent): InlineNodeInterface
    {
        $content = u($content);
        if (!$content->containsAny('::')) {
            throw new \RuntimeException(sprintf('Malformed method reference "%s"', $content));
        }
        [$fqcn, $method] = $content->replace('\\\\', '\\')->split('::', 2);

        $filename = \sprintf('%s.php#:~:text=%s', $fqcn->replace('\\', '/'), rawurlencode('function '.$method));
        $url = \sprintf($this->buildConfig->symfonyRepositoryUrl, $filename);

        return new ExternalLinkNode($url, $method.'()', $fqcn.'::'.$method.'()');
    }

    #[\Override]
    public function getName(): string
    {
        return 'method';
    }

    #[\Override]
    public function getAliases(): array
    {
        return [];
    }
}
