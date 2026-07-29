<?php

/*
 * This file is part of the Docs Builder package.
 * (c) Ryan Weaver <ryan@symfonycasts.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SymfonyDocsBuilder\Tests\Twig;

use PHPUnit\Framework\TestCase;
use SymfonyDocsBuilder\Twig\TocExtension;

class TocExtensionTest extends TestCase
{
    /**
     * @dataProvider getMaxDepthTests
     */
    public function testItemsDeeperThanMaxDepthAreNotCounted(int $maxDepth, int $expectedNumVisibleItems, string $expectedSize)
    {
        $options = TocExtension::getOptions($this->createToc(), $maxDepth);

        $this->assertSame($maxDepth, $options['maxDepth']);
        $this->assertSame($expectedNumVisibleItems, $options['numVisibleItems']);
        $this->assertSame($expectedSize, $options['size']);
    }

    public function getMaxDepthTests()
    {
        // the TOC below has 2 items on level 1, 4 on level 2 and 8 on level 3
        yield 'level 1 only' => [1, 2, 'md'];
        yield 'levels 1 and 2' => [2, 6, 'md'];
        yield 'levels 1 to 3' => [3, 14, 'lg'];
    }

    public function testTheDefaultMaxDepthIsTwo()
    {
        $options = TocExtension::getOptions($this->createToc());

        $this->assertSame(2, $options['maxDepth']);
        $this->assertSame(6, $options['numVisibleItems']);
    }

    /**
     * Builds a TOC with 2 items on level 1, each having 2 children on level 2,
     * each of them having 2 children on level 3.
     */
    private function createToc(): array
    {
        $toc = [];

        for ($i = 1; $i <= 2; ++$i) {
            $level1 = $this->createItem(1);

            for ($j = 1; $j <= 2; ++$j) {
                $level2 = $this->createItem(2);

                for ($k = 1; $k <= 2; ++$k) {
                    $level2['children'][] = $this->createItem(3);
                }

                $level1['children'][] = $level2;
            }

            $toc[] = $level1;
        }

        return $toc;
    }

    private function createItem(int $level): array
    {
        return ['level' => $level, 'children' => []];
    }
}
