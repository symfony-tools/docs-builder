<?php

declare(strict_types=1);

/*
 * This file is part of the Docs Builder package.
 * (c) Ryan Weaver <ryan@symfonycasts.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SymfonyDocsBuilder\Generator;

use Doctrine\RST\Environment;
use Doctrine\RST\Meta\MetaEntry;
use function Symfony\Component\String\u;

/**
 * It encapsulates all the logic needed to generate the full TOC items
 * from the given doc meta entries and provides some TOC utilities.
 */
class TocGenerator
{
    private $metaEntry;
    private $cachedToc;

    public function __construct(MetaEntry $metaEntry)
    {
        $this->metaEntry = $metaEntry;
    }

    public function getToc(): array
    {
        if (null !== $this->cachedToc) {
            return $this->cachedToc;
        }

        return $this->cachedToc = $this->doGenerateToc($this->metaEntry, current($this->metaEntry->getTitles())[1]);
    }

    public function getFlatenedToc(): array
    {
        return $this->doFlattenToc($this->getToc());
    }

    /**
     * Returns the number of TOC items per indentation level
     * (which correspond to <h1>, <h2>, <h3>, etc. elements)
     */
    public function getNumItemsPerLevel(): array
    {
        $numItems = [1 => 0, 2 => 0, 3 => 0, 4 => 0, 5 => 0, 6 => 0];
        foreach ($this->getFlatenedToc() as $tocItem) {
            ++$numItems[$tocItem['level']];
        }

        $numItems['total'] = array_sum($numItems);

        return $numItems;
    }

    private function doGenerateToc(MetaEntry $metaEntry, ?array $titles, $level = 1): array
    {
        if (null === $titles) {
            return [];
        }

        $toc = [];
        foreach ($titles as $title) {
            $toc[] = [
                'url' => sprintf('%s#%s', $metaEntry->getUrl(), Environment::slugify($title[0])),
                'page' => u($metaEntry->getUrl())->beforeLast('.html')->toString(),
                'fragment' => Environment::slugify($title[0]),
                'title' => $title[0],
                'level' => $level,
                'children' => $this->doGenerateToc($metaEntry, $title[1], $level + 1),
            ];
        }

        return $toc;
    }

    private function doFlattenToc(array $toc, array &$flattenedToc = []): array
    {
        foreach ($toc as $item) {
            $flattenedToc[] = $item;

            if ([] !== $item['children']) {
                $this->doFlattenToc($item['children'], $flattenedToc);
            }
        }

        return $flattenedToc;
    }
}
