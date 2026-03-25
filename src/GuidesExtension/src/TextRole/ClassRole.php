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

        if (str_starts_with($fqcn, 'Symfony\\AI\\')) {
            /**
             * Symfony AI classes require some special handling because of its monorepo structure. Example:
             *
             *     input: Symfony\AI\Agent\Memory\StaticMemoryProvider
             *     output: https://github.com/symfony/ai/blob/main/src/agent/src/Memory/StaticMemoryProvider.php
             */
            $classPath = $fqcn->after('Symfony\\AI\\');
            [$monorepoSubRepository, $classRelativePath] = $classPath->split('\\', 2);
            // because of monorepo structure, the first part of the classpath needs to be slugged
            // 'Agent' -> 'agent', 'AiBundle' -> 'ai-bundle', etc.
            $monorepoSubRepository = $monorepoSubRepository->snake()->replace('_', '-')->lower();
            $classRelativePath = $classRelativePath->replace('\\', '/');

            $url = \sprintf('https://github.com/symfony/ai/blob/main/src/%s/src/%s.php', $monorepoSubRepository, $classRelativePath);
        } elseif (str_starts_with($fqcn, 'Symfony\\UX\\')) {
            /**
             * Symfony UX classes require some special handling because of its monorepo structure. Example:
             *
             *     input: Symfony\UX\Chartjs\Twig\ChartExtension
             *     output: https://github.com/symfony/ux/blob/2.x/src/Chartjs/src/Twig/ChartExtension.php
             */
            $classPath = $fqcn->after('Symfony\\UX\\');
            [$monorepoSubRepository, $classRelativePath] = $classPath->split('\\', 2);
            $classRelativePath = $classRelativePath->replace('\\', '/');

            $url = \sprintf('https://github.com/symfony/ux/blob/2.x/src/%s/src/%s.php', $monorepoSubRepository, $classRelativePath);
        } else {
            $url = \sprintf($this->buildConfig->symfonyRepositoryUrl, $fqcn->replace('\\', '/').'.php');
        }

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
