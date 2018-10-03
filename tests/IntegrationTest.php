<?php

namespace SymfonyCasts\Tests;

use Doctrine\RST\Builder;
use Doctrine\RST\Parser;
use Gajus\Dindent\Indenter;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;
use SymfonyDocs\HtmlKernel;

class IntegrationTest extends TestCase
{
    /**
     * @dataProvider integrationProvider
     */
    public function testIntegration(string $folder)
    {
        $builder = $this->createBuilder();

        $builder->build(
            sprintf('%s/fixtures/source/%s', __DIR__, $folder),
            __DIR__.'/_output',
            false // verbose
        );

        $finder = new Finder();
        $finder->in(sprintf('%s/fixtures/expected/%s', __DIR__, $folder))
            ->files()
            ->depth('>=0');

        $indenter = new Indenter();
        foreach ($finder as $expectedFile) {
            $relativePath   = $expectedFile->getRelativePathname();
            $actualFilename = __DIR__.'/_output/'.$relativePath;
            $this->assertFileExists($actualFilename);

            $this->assertSame(
                // removes odd trailing space the indenter is adding
                str_replace(" \n", "\n", $indenter->indent($expectedFile->getContents())),
                str_replace(" \n", "\n", $indenter->indent(file_get_contents($actualFilename))),
                sprintf('File %s is not equal', $relativePath)
            );
        }
    }

    public function integrationProvider()
    {
        yield 'main' => [
            'folder' => 'main',
        ];

        yield 'toctree' => [
            'folder' => 'toctree',
        ];

        yield 'ref-reference' => [
            'folder' => 'ref-reference',
        ];

        yield 'doc-reference' => [
            'folder' => 'doc-reference',
        ];
    }

    /**
     * @dataProvider parserUnitBlockProvider
     */
    public function testParseUnitBlock(string $blockName)
    {
        $kernel = new HtmlKernel();
        $parser = new Parser(null, $kernel);

        $sourceFile = sprintf('%s/fixtures/source/blocks/%s.rst', __DIR__, $blockName);

        $document = $parser->parseFile($sourceFile)->renderDocument();

        $indenter = new Indenter();

        $expectedFile = sprintf('%s/fixtures/expected/blocks/%s.html', __DIR__, $blockName);
        $this->assertSame(
            // removes odd trailing space the indenter is adding
            str_replace(" \n", "\n", $indenter->indent(file_get_contents($expectedFile))),
            str_replace(" \n", "\n", $indenter->indent($document))
        );
    }

    public function parserUnitBlockProvider()
    {
        yield 'tables' => [
            'blockName' => 'tables',
        ];

        yield 'caution' => [
            'blockName' => 'caution',
        ];

        yield 'note' => [
            'blockName' => 'note',
        ];

        yield 'seealso' => [
            'blockName' => 'seealso',
        ];

        yield 'tip' => [
            'blockName' => 'tip',
        ];

        yield 'versionadded' => [
            'blockName' => 'versionadded',
        ];

        yield 'class' => [
            'blockName' => 'class',
        ];

        yield 'configuration-block' => [
            'blockName' => 'configuration-block',
        ];

        yield 'sidebar' => [
            'blockName' => 'sidebar',
        ];

        yield 'note-code-block-nested' => [
            'blockName' => 'note-code-block-nested',
        ];

        yield 'sidebar-code-block-nested' => [
            'blockName' => 'sidebar-code-block-nested',
        ];

        yield 'literal' => [
            'blockName' => 'literal',
        ];

        yield 'class-reference' => [
            'blockName' => 'references/class',
        ];

        yield 'namespace-reference' => [
            'blockName' => 'references/namespace',
        ];

        yield 'method-reference' => [
            'blockName' => 'references/method',
        ];

        yield 'php-class-reference' => [
            'blockName' => 'references/php-class',
        ];

        yield 'php-function-reference' => [
            'blockName' => 'references/php-function',
        ];

        yield 'php-method-reference' => [
            'blockName' => 'references/php-method',
        ];

        yield 'code-block-php' => [
            'blockName' => 'code-blocks/php',
        ];

        yield 'code-block-html' => [
            'blockName' => 'code-blocks/html',
        ];

        yield 'code-block-twig' => [
            'blockName' => 'code-blocks/twig',
        ];

        yield 'code-block-html-twig' => [
            'blockName' => 'code-blocks/html-twig',
        ];

        yield 'code-block-xml' => [
            'blockName' => 'code-blocks/xml',
        ];

        yield 'code-block-yaml' => [
            'blockName' => 'code-blocks/yaml',
        ];

        yield 'code-block-ini' => [
            'blockName' => 'code-blocks/ini',
        ];

        yield 'code-block-bash' => [
            'blockName' => 'code-blocks/bash',
        ];

        yield 'code-block-html-php' => [
            'blockName' => 'code-blocks/html-php',
        ];

        yield 'code-block-php-annotations' => [
            'blockName' => 'code-blocks/php-annotations',
        ];

        yield 'code-block-text' => [
            'blockName' => 'code-blocks/text',
        ];

        yield 'code-block-terminal' => [
            'blockName' => 'code-blocks/terminal',
        ];

        yield 'list' => [
            'blockName' => 'list',
        ];
    }

    public function testRefReferenceError()
    {
        $this->expectException(\RuntimeException::class);

        $this->createBuilder()->build(
            sprintf('%s/fixtures/source/ref-reference-error', __DIR__),
            __DIR__.'/_output',
            false // verbose
        );
    }

    private function createBuilder(): Builder
    {
        $kernel  = new HtmlKernel();
        $builder = new Builder($kernel);
        $fs      = new Filesystem();
        $fs->remove(__DIR__.'/_output');

        return $builder;
    }
}
