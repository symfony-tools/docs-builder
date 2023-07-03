<?php

namespace SymfonyTools\GuidesExtension\Tests;

use League\Flysystem\Adapter\Local;
use PHPUnit\Framework\ExpectationFailedException;
use PHPUnit\Framework\TestCase;
use SymfonyTools\GuidesExtension\Build\DynamicBuildEnvironment;
use SymfonyTools\GuidesExtension\DocBuilder;
use SymfonyTools\GuidesExtension\DocsKernel;
use SymfonyTools\GuidesExtension\Test\HtmlAsserter;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\Finder\Finder;

class HtmlIntegrationTest extends TestCase
{
    /** @dataProvider provideBlocks */
    public function testBlocks(string $sourceFile, string $expectedFile)
    {
        $expectedContents = file_get_contents($expectedFile);
        $skip = false;
        if (str_starts_with($expectedContents, 'SKIP')) {
            $skip = trim(substr(strstr($expectedContents, "\n", true), 4));
            $expectedContents = strstr($expectedContents, "\n");
        }

        try {
            $generatedContents = DocsKernel::create()->get(DocBuilder::class)->buildString(file_get_contents($sourceFile));
            $generated = new \DOMDocument();
            $generated->loadHTML($generatedContents, \LIBXML_NOERROR);
            $generated->preserveWhiteSpace = false;
            $generatedHtml = $this->sanitizeHTML($generated->saveHTML());

            $expected = new \DOMDocument();
            $expectedContents = "<!DOCTYPE html>\n<html>\n<body>\n".$expectedContents."\n</body>\n</html>";
            $expected->loadHTML($expectedContents, \LIBXML_NOERROR);
            $expected->preserveWhiteSpace = false;
            $expectedHtml = $this->sanitizeHTML($expected->saveHTML());

            $this->assertEquals($expectedHtml, $generatedHtml);
        } catch (ExpectationFailedException $e) {
            if (false !== $skip) {
                $this->markTestIncomplete($skip);
            }

            throw $e;
        }

        $this->assertFalse($skip, 'Test passes while marked as SKIP.');
    }

    public static function provideBlocks(): iterable
    {
        foreach ((new Finder())->files()->in(__DIR__.'/fixtures/source/blocks') as $file) {
            yield $file->getRelativePathname() => [$file->getRealPath(), __DIR__.'/fixtures/expected/blocks/'.str_replace('.rst', '.html', $file->getRelativePathname())];
        }
    }

    /** @dataProvider provideProjects */
    public function testProjects(string $directory)
    {
        $buildEnvironment = new DynamicBuildEnvironment(new Local(__DIR__.'/fixtures/source/'.$directory));
        
        DocsKernel::create()->get(DocBuilder::class)->build($buildEnvironment);

        $expectedDirectory = __DIR__.'/fixtures/expected/'.$directory;
        $skip = false;
        if (file_exists($expectedDirectory.'/skip')) {
            $skip = file_get_contents($expectedDirectory.'/skip');
        }

        try {
            foreach ((new Finder())->files()->in($expectedDirectory) as $file) {
                $expected = $this->sanitizeHTML($file->getContents());
                $actual = $this->sanitizeHTML($buildEnvironment->getOutputFilesystem()->read($file->getRelativePathname()));

                $this->assertEquals($expected, $actual, 'File: '.$file->getRelativePathname());
            }
        } catch (ExpectationFailedException $e) {
            if (false !== $skip) {
                $this->markTestIncomplete($skip);
            }

            throw $e;
        }

        $this->assertFalse($skip, 'Test passes while marked as SKIP.');
    }

    public function provideProjects(): iterable
    {
        foreach ((new Finder())->directories()->in(__DIR__.'/fixtures/source')->depth(0) as $dir) {
            if ('blocks' === $dir->getBasename()) {
                continue;
            }

            yield $dir->getBasename() => [$dir->getBasename()];
        }
    }

    private function sanitizeHTML(string $html): string
    {
        $html = implode("\n", array_map('trim', explode("\n", $html)));
        $html = preg_replace('# +#', ' ', $html);
        $html = preg_replace('# <#', '<', $html);
        $html = preg_replace('#> #', '>', $html);
        $html = preg_replace('#\R+#', "\n", $html);

        $html = substr($html, strpos($html, '<body>') + 6);
        $html = substr($html, 0, strpos($html, '</body>'));

        return trim($html);
    }
}
