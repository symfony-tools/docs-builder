<?php

/*
 * This file is part of the Docs Builder package.
 * (c) Ryan Weaver <ryan@symfonycasts.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SymfonyDocsBuilder\Tests\Renderers;

use PHPUnit\Framework\TestCase;
use SymfonyDocsBuilder\Renderers\CodeNodeRenderer;

class CodeNodeRendererTest extends TestCase
{
    /**
     * @dataProvider getSupportedLanguageTests
     */
    public function testIsLanguageSupported(string $lang, bool $shouldBeSupported)
    {
        $this->assertSame($shouldBeSupported, CodeNodeRenderer::isLanguageSupported($lang));
    }

    public function getSupportedLanguageTests()
    {
        yield 'normal_lang' => ['xml', true];
        yield 'aliased_lang' => ['php-annotations', true];
        yield 'unsupported' => ['bhb', false];
    }
}
