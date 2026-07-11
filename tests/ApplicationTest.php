<?php

/*
 * This file is part of the Docs Builder package.
 * (c) Ryan Weaver <ryan@symfonycasts.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SymfonyDocsBuilder\Tests;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Process\Process;
use SymfonyDocsBuilder\BuildConfig;

/**
 * The rest of the test suite drives the command through CommandTester, so it
 * never runs the binary itself. This makes sure the binary at least boots.
 */
class ApplicationTest extends TestCase
{
    public function testTheBinaryRegistersTheBuildDocsCommand()
    {
        $process = new Process([\PHP_BINARY, __DIR__.'/../bin/docs-builder', 'list', '--no-ansi']);
        $process->run();

        $this->assertSame(
            0,
            $process->getExitCode(),
            sprintf("The binary failed to run:\n%s", $process->getErrorOutput())
        );
        $this->assertStringContainsString('build:docs', $process->getOutput());
    }

    public function testTheSymfonyVersionOptionIsUsedToBuildTheDocs()
    {
        $this->assertStringContainsString(
            'github.com/symfony/symfony/blob/6.4/src',
            $this->buildAndGetHtml(['--symfony-version=6.4'])
        );
    }

    public function testTheSymfonyVersionEnvVarIsUsedToBuildTheDocs()
    {
        $this->assertStringContainsString(
            'github.com/symfony/symfony/blob/7.4/src',
            $this->buildAndGetHtml([], ['SYMFONY_VERSION' => '7.4'])
        );
    }

    public function testTheDefaultSymfonyVersionIsUsedWhenNoneIsGiven()
    {
        $this->assertStringContainsString(
            sprintf('github.com/symfony/symfony/blob/%s/src', (new BuildConfig())->getSymfonyVersion()),
            $this->buildAndGetHtml()
        );
    }

    /**
     * Builds the "main" fixture through the binary and returns the HTML of the
     * page holding a :class: reference.
     */
    private function buildAndGetHtml(array $options = [], array $env = []): string
    {
        $outputDir = sprintf('%s/_output/application/%s', __DIR__, uniqid());

        $process = new Process(
            array_merge(
                [\PHP_BINARY, __DIR__.'/../bin/docs-builder', 'build:docs', __DIR__.'/fixtures/source/main', $outputDir],
                $options
            ),
            null,
            $env
        );
        $process->run();

        $this->assertSame(
            0,
            $process->getExitCode(),
            sprintf("The binary failed to run:\n%s", $process->getErrorOutput())
        );

        $html = file_get_contents($outputDir.'/datetime.html');

        (new Filesystem())->remove($outputDir);

        return $html;
    }
}
