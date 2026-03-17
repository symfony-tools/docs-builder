<?php

/*
 * This file is part of the Docs Builder package.
 * (c) Ryan Weaver <ryan@symfonycasts.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SymfonyTools\DocsBuilder\Tests\GuidesExtension\Highlighter;

use Highlight\Highlighter;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;
use SymfonyTools\DocsBuilder\GuidesExtension\Highlighter\SymfonyHighlighter;
use phpDocumentor\Guides\Code\Highlighter\HighlightPhpHighlighter;

class SymfonyHighlighterTest extends TestCase
{
    public static function setUpBeforeClass(): void
    {
        Highlighter::registerLanguage('php', dirname(__DIR__, 3).'/src/GuidesExtension/resources/highlight.php/php.json', true);
        Highlighter::registerLanguage('twig', dirname(__DIR__, 3).'/src/GuidesExtension/resources/highlight.php/twig.json', true);
    }

    #[DataProvider('getRenderingTests')]
    public function testCustomRendering(string $language, string $inputFile, string $outputFile): void
    {
        $highlighter = new SymfonyHighlighter(new HighlightPhpHighlighter(new Highlighter(), new NullLogger()));
        $actual = $highlighter(
            $language,
            file_get_contents(__DIR__.'/fixtures/'.$inputFile),
            []
        )->code;

        $this->assertSame(file_get_contents(__DIR__.'/fixtures/'.$outputFile), $actual);
    }

    public static function getRenderingTests(): iterable
    {
        yield 'php' => [
            'php',
            'php.input.txt',
            'php.output.html',
        ];

        yield 'twig' => [
            'twig',
            'twig.input.txt',
            'twig.output.html',
        ];
    }
}
