<?php

/*
 * This file is part of the Docs Builder package.
 * (c) Ryan Weaver <ryan@symfonycasts.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SymfonyDocsBuilder\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class TocExtension extends AbstractExtension
{
    public function getFunctions(): array
    {
        return [
            new TwigFunction('toc_options', [$this, 'getOptions']),
        ];
    }

    public static function getOptions(array $toc): array
    {
        $flattendToc = self::flattenToc($toc);
        $maxDepth = 0;
        $numVisibleItems = 0;
        foreach ($flattendToc as $tocItem) {
            $maxDepth = max($maxDepth, $tocItem['level']);
            $numVisibleItems++;
        }

        return [
            'maxDepth' => $maxDepth,
            'numVisibleItems' => $numVisibleItems,
            'size' => self::getTocSize($numVisibleItems),
        ];
    }

    private static function flattenToc(array $toc, array &$flattenedToc = []): array
    {
        foreach ($toc as $item) {
            $flattenedToc[] = $item;

            if ([] !== $item['children']) {
                self::flattenToc($item['children'], $flattenedToc);
            }
        }

        return $flattenedToc;
    }

    private static function getTocSize(int $numVisibleItems): string
    {
        if ($numVisibleItems < 10) {
            return 'md';
        }

        if ($numVisibleItems < 20) {
            return 'lg';
        }

        return 'xl';
    }
}
