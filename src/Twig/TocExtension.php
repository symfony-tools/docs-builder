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
    /**
     * Same default as Doctrine\RST\Nodes\TocNode::DEFAULT_DEPTH.
     */
    private const DEFAULT_MAX_DEPTH = 2;

    public function getFunctions(): array
    {
        return [
            new TwigFunction('toc_options', [$this, 'getOptions']),
        ];
    }

    /**
     * $maxDepth defaults to the same value as the 'maxdepth' option of the
     * 'toctree' directive, which is what the TOC of a page is built upon.
     *
     * @see https://www.sphinx-doc.org/en/master/usage/restructuredtext/directives.html
     */
    public static function getOptions(array $toc, int $maxDepth = self::DEFAULT_MAX_DEPTH): array
    {
        $flattendToc = self::flattenToc($toc);
        $numVisibleItems = 0;
        foreach ($flattendToc as $tocItem) {
            if ($tocItem['level'] > $maxDepth) {
                continue;
            }

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
