<?php

namespace SymfonyDocsBuilder\Tests\Templates;

use Highlight\Highlighter;
use PHPUnit\Framework\TestCase;

class HighlightJsIntegrationTest extends TestCase
{
    public static function setUpBeforeClass(): void
    {
        Highlighter::registerLanguage('php', __DIR__.'/../../src/Templates/highlight.php/php.json', true);
        Highlighter::registerLanguage('twig', __DIR__.'/../../src/Templates/highlight.php/twig.json', true);
    }

    /**
     * @dataProvider getRenderingTests
     */
    public function testCustomRendering(string $language, string $inputFile, string $outputFile)
    {
        $highlighter = new Highlighter();
        $actual = $highlighter->highlight(
            $language,
            file_get_contents(__DIR__.'/fixtures/'.$inputFile)
        )->value;

        $this->assertSame(file_get_contents(__DIR__.'/fixtures/'.$outputFile), $actual);
    }

    public function getRenderingTests()
    {
        yield 'php' => [
            'php',
            'php.input.txt',
            'php.output.html'
        ];

        yield 'twig' => [
            'twig',
            'twig.input.txt',
            'twig.output.html'
        ];
    }
}
