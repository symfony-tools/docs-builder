<?php

/*
 * This file is part of the Docs Builder package.
 * (c) Ryan Weaver <ryan@symfonycasts.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SymfonyDocsBuilder\Tests;

use Doctrine\RST\Builder;
use Doctrine\RST\Configuration;
use Doctrine\RST\Meta\CachedMetasLoader;
use Doctrine\RST\Meta\Metas;
use Doctrine\RST\Parser;
use Gajus\Dindent\Indenter;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;
use SymfonyDocsBuilder\BuildContext;
use SymfonyDocsBuilder\Generator\JsonGenerator;
use SymfonyDocsBuilder\KernelFactory;

class IntegrationTest extends TestCase
{
    public function setUp(): void
    {
        $fs = new Filesystem();
        $fs->remove(__DIR__.'/../var');
    }

    /**
     * @dataProvider integrationProvider
     */
    public function testIntegration(string $folder)
    {
        $fs = new Filesystem();
        $fs->remove(__DIR__.'/_output');
        $fs->mkdir(__DIR__.'/_output');

        $buildContext = $this->createBuildContext(sprintf('%s/fixtures/source/%s', __DIR__, $folder));

        $builder = new Builder(
            KernelFactory::createKernel($buildContext)
        );

        $builder->build(
            sprintf('%s/fixtures/source/%s', __DIR__, $folder),
            __DIR__.'/_output'
        );

        $finder = new Finder();
        $finder->in(sprintf('%s/fixtures/expected/%s', __DIR__, $folder))
            ->files()
            ->depth('>=0');

        $indenter = $this->createIndenter();
        foreach ($finder as $expectedFile) {
            $relativePath = $expectedFile->getRelativePathname();
            $actualFilename = __DIR__.'/_output/'.$relativePath;
            $this->assertFileExists($actualFilename);

            $this->assertSame(
                // removes odd trailing space the indenter is adding
                str_replace(" \n", "\n", $indenter->indent($expectedFile->getContents())),
                str_replace(" \n", "\n", $indenter->indent(file_get_contents($actualFilename))),
                sprintf('File %s is not equal', $relativePath)
            );
        }

        /*
         * TODO - get this from the Builder when it is exposed
         * https://github.com/doctrine/rst-parser/pull/97
         */
        $metas = new Metas();
        $cachedMetasLoader = new CachedMetasLoader();
        $cachedMetasLoader->loadCachedMetaEntries(__DIR__.'/_output', $metas);

        $jsonGenerator = new JsonGenerator($metas, $buildContext);
        $jsonGenerator->generateJson(new ProgressBar(new NullOutput()));

        foreach ($finder as $htmlFile) {
            $relativePath = $htmlFile->getRelativePathname();
            $actualFilename = __DIR__.'/_output/'.str_replace('.html', '.fjson', $relativePath);
            $this->assertFileExists($actualFilename);

            $jsonData = json_decode(file_get_contents($actualFilename), true);
            $crawler = new Crawler($htmlFile->getContents());

            $this->assertSame(
                str_replace(" \n", "\n", $indenter->indent($crawler->filter('body')->html())),
                str_replace(" \n", "\n", $indenter->indent($jsonData['body']))
            );
            $this->assertSame($crawler->filter('h1')->first()->text(), $jsonData['title']);
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
        $configuration = new Configuration();
        $configuration->setCustomTemplateDirs([__DIR__.'/Templates']);

        $parser = new Parser(
            KernelFactory::createKernel($this->createBuildContext(sprintf('%s/fixtures/source/blocks', __DIR__)))
        );

        $sourceFile = sprintf('%s/fixtures/source/blocks/%s.rst', __DIR__, $blockName);

        $document = $parser->parseFile($sourceFile)->renderDocument();

        $indenter = $this->createIndenter();

        $expectedFile = sprintf('%s/fixtures/expected/blocks/%s.html', __DIR__, $blockName);
        $this->assertSame(
            str_replace(" \n", "\n", $indenter->indent(file_get_contents($expectedFile))),
            str_replace(" \n", "\n", $indenter->indent($document))
        );
    }

    public function parserUnitBlockProvider()
    {
        yield 'tables' => [
            'blockName' => 'nodes/tables',
        ];

        yield 'literal' => [
            'blockName' => 'nodes/literal',
        ];

        yield 'list' => [
            'blockName' => 'nodes/list',
        ];

        yield 'caution' => [
            'blockName' => 'directives/caution',
        ];

        yield 'note' => [
            'blockName' => 'directives/note',
        ];

        yield 'admonition' => [
            'blockName' => 'directives/admonition',
        ];

        yield 'note-code-block-nested' => [
            'blockName' => 'directives/note-code-block-nested',
        ];

        yield 'seealso' => [
            'blockName' => 'directives/seealso',
        ];

        yield 'tip' => [
            'blockName' => 'directives/tip',
        ];

        yield 'topic' => [
            'blockName' => 'directives/topic',
        ];

        yield 'best-practice' => [
            'blockName' => 'directives/best-practice',
        ];

        yield 'versionadded' => [
            'blockName' => 'directives/versionadded',
        ];

        yield 'class' => [
            'blockName' => 'directives/class',
        ];

        yield 'configuration-block' => [
            'blockName' => 'directives/configuration-block',
        ];

        yield 'sidebar' => [
            'blockName' => 'directives/sidebar',
        ];

        yield 'sidebar-code-block-nested' => [
            'blockName' => 'directives/sidebar-code-block-nested',
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
    }

    private function createBuildContext(string $sourceDir): BuildContext
    {
        $buildContext = new BuildContext(
            '4.0',
            'https://api.symfony.com/4.0',
            'https://secure.php.net/manual/en',
            'https://symfony.com/doc/4.0'
        );
        $buildContext->initializeRuntimeConfig(
            $sourceDir,
            __DIR__.'/_output',
            null
        );
        $buildContext->setCacheDirectory(__DIR__.'/_cache');

        return $buildContext;
    }

    private function createIndenter(): Indenter
    {
        $indenter = new Indenter();
        // indent spans - easier to debug failures
        $indenter->setElementType('span', Indenter::ELEMENT_TYPE_BLOCK);

        return $indenter;
    }
}
