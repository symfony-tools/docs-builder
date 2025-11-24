<?php

/*
 * This file is part of the Docs Builder package.
 * (c) Ryan Weaver <ryan@symfonycasts.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SymfonyDocsBuilder\Reference;

use Doctrine\RST\Environment;
use Doctrine\RST\References\Reference;
use Doctrine\RST\References\ResolvedReference;
use function Symfony\Component\String\u;

class ClassReference extends Reference
{
    private $symfonyRepositoryUrl;

    public function __construct(string $symfonyRepositoryUrl)
    {
        $this->symfonyRepositoryUrl = $symfonyRepositoryUrl;
    }

    public function getName(): string
    {
        return 'class';
    }

    public function resolve(Environment $environment, string $data): ResolvedReference
    {
        $className = u($data)->replace('\\\\', '\\');

        /**
         * Symfony AI classes require some special handling because of its monorepo structure. Example:
         *
         *     input: Symfony\AI\Agent\Memory\StaticMemoryProvider
         *     output: https://github.com/symfony/ai/blob/main/src/agent/src/Memory/StaticMemoryProvider.php
         */
        if (str_starts_with($className, 'Symfony\\AI\\')) {
            $classPath = $className->after('Symfony\\AI\\');
            [$monorepoSubRepository, $classRelativePath] = $classPath->split('\\', 2);
            // because of monorepo structure, the first part of the classpath needs to be slugged
            // 'Agent' -> 'agent', 'AiBundle' -> 'ai-bundle', etc.
            $monorepoSubRepository = $monorepoSubRepository->snake('-')->lower();
            $classRelativePath = $classRelativePath->replace('\\', '/');

            $url = \sprintf('https://github.com/symfony/ai/blob/main/src/%s/src/%s.php', $monorepoSubRepository, $classRelativePath);
        /**
         * Symfony UX classes require some special handling because of its monorepo structure. Example:
         *
         *     input: Symfony\UX\Chartjs\Twig\ChartExtension
         *     output: https://github.com/symfony/ux/blob/2.x/src/Chartjs/src/Twig/ChartExtension.php
         */
        } elseif (str_starts_with($className, 'Symfony\\UX\\')) {
            $classPath = $className->after('Symfony\\UX\\');
            [$monorepoSubRepository, $classRelativePath] = $classPath->split('\\', 2);
            $classRelativePath = $classRelativePath->replace('\\', '/');

            $url = \sprintf('https://github.com/symfony/ux/blob/2.x/src/%s/src/%s.php', $monorepoSubRepository, $classRelativePath);
        } else {
            $url = sprintf('%s/%s.php', $this->symfonyRepositoryUrl, $className->replace('\\', '/'));
        }

        return new ResolvedReference(
            $environment->getCurrentFileName(),
            $className->afterLast('\\'),
            $url,
            [],
            [
                'title' => $className,
            ]
        );
    }
}
