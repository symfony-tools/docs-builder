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
    public function testIntegration()
    {
        $kernel  = new HtmlKernel();
        $builder = new Builder($kernel);
        $fs      = new Filesystem();
        $fs->remove(__DIR__.'/_output');

        $builder->build(
            __DIR__.'/fixtures/source',
            __DIR__.'/_output',
            true // verbose
        );

        $finder = new Finder();
        $finder->in(__DIR__.'/fixtures/expected/')
            ->files()
            ->depth('>=0');

        $indenter = new Indenter();
        foreach ($finder as $expectedFile) {
            $relativePath = $expectedFile->getRelativePathname();
            if ($relativePath !== 'tables.html') {
                continue;
            }

            $actualFilename = __DIR__.'/_output/'.$relativePath;
            $this->assertFileExists($actualFilename);

            $expectedSource = $indenter->indent($expectedFile->getContents());
            $actualSource   = $indenter->indent(file_get_contents($actualFilename));
            $this->assertSame($expectedSource, $actualSource, sprintf('File %s is not equal', $relativePath));
        }
    }

    /**
     * @dataProvider parserProvider
     */
    public function testParser(string $blockName)
    {
        $kernel = new HtmlKernel();
        $parser = new Parser(null, $kernel);

        $sourceFile = sprintf('%s/fixtures/source/%s.rst', __DIR__, $blockName);

        $document = $parser->parseFile($sourceFile)->renderDocument();

        $indenter = new Indenter();

        $expectedFile = sprintf('%s/fixtures/expected/%s.html', __DIR__, $blockName);
        $this->assertSame(
            $indenter->indent(file_get_contents($expectedFile)),
            $indenter->indent($document)
        );
    }

    public function parserProvider()
    {
        yield 'tables' => [
            'documentName' => 'tables'
        ];
    }
}
