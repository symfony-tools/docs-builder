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
        $this->createBuilder()->build(
            sprintf('%s/fixtures/source/%s', __DIR__, $folder),
            __DIR__.'/_output',
            true // verbose
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

            $expectedSource = $indenter->indent($expectedFile->getContents());
            $actualSource   = $indenter->indent(file_get_contents($actualFilename));
            $this->assertSame($expectedSource, $actualSource, sprintf('File %s is not equal', $relativePath));
        }
    }

    public function integrationProvider()
    {
        //        yield 'main' => [
        //            'folder' => 'main'
        //        ];

        yield 'toctree' => [
            'folder' => 'toctree',
        ];

        yield 'refReference' => [
            'folder' => 'refReference',
        ];

        yield 'refReferenceError' => [
            'folder' => 'refReferenceError',
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
            $indenter->indent(file_get_contents($expectedFile)),
            $indenter->indent($document)
        );
    }

    public function parserUnitBlockProvider()
    {
        yield 'tables' => [
            'documentName' => 'tables',
        ];

        yield 'caution' => [
            'documentName' => 'caution',
        ];

        yield 'note' => [
            'documentName' => 'note',
        ];

        yield 'seealso' => [
            'documentName' => 'seealso',
        ];

        yield 'tip' => [
            'documentName' => 'tip',
        ];

        yield 'versionadded' => [
            'documentName' => 'versionadded',
        ];

        yield 'class' => [
            'documentName' => 'class',
        ];

        yield 'configuration-block' => [
            'documentName' => 'configuration-block',
        ];

        yield 'code-block' => [
            'documentName' => 'code-block',
        ];

        yield 'sidebar' => [
            'documentName' => 'sidebar',
        ];

        yield 'note-code-block-nested' => [
            'documentName' => 'note-code-block-nested',
        ];

        yield 'sidebar-code-block-nested' => [
            'documentName' => 'sidebar-code-block-nested',
        ];

        yield 'literal' => [
            'documentName' => 'literal',
        ];
    }

    public function testRefReferenceError()
    {
        $this->expectException(\RuntimeException::class);

        $this->createBuilder()->build(
            sprintf('%s/fixtures/source/refReferenceError', __DIR__),
            __DIR__.'/_output',
            true // verbose
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
