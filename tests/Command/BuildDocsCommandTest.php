<?php

declare(strict_types=1);

/*
 * This file is part of the Docs Builder package.
 * (c) Ryan Weaver <ryan@symfonycasts.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Tests\Command;

use Gajus\Dindent\Indenter;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;
use SymfonyDocsBuilder\BuildConfig;
use SymfonyDocsBuilder\Command\BuildDocsCommand;

class BuildDocsCommandTest extends TestCase
{
    public function testBuildDocsDefault()
    {
        $buildConfig = $this->createBuildConfig();
        $outputDir = __DIR__.'/../_output';

        $filesystem = new Filesystem();
        $filesystem->remove($outputDir);
        $filesystem->mkdir($outputDir);

        $output = $this->executeCommand(
            $buildConfig,
            [
                'source-dir' => __DIR__.'/../fixtures/source/main',
                'output-dir' => $outputDir,
            ]
        );

        $this->assertStringContainsString('[OK] Build complete', $output);

        $this->assertTrue($filesystem->exists(sprintf('%s/_images/symfony-logo.png', $outputDir)));

        $output = $this->executeCommand(
            $buildConfig,
            [
                'source-dir' => __DIR__.'/../fixtures/source/main',
                'output-dir' => $outputDir,
            ]
        );
        $this->assertStringContainsString('[OK] Build complete', $output);
    }

    public function testBuildDocsForPdf()
    {
        $buildConfig = $this->createBuildConfig();

        $fs = new Filesystem();
        if ($fs->exists($buildConfig->getOutputDir())) {
            $fs->remove($buildConfig->getOutputDir());
            $fs->mkdir($buildConfig->getOutputDir());
        }

        $output = $this->executeCommand(
            $buildConfig,
            [
                'source-dir' => __DIR__.'/../fixtures/source/build-pdf',
                'output-dir' => $buildConfig->getOutputDir(),
                '--parse-sub-path' => 'book',
            ]
        );

        $filesystem = new Filesystem();
        $this->assertTrue($filesystem->exists(sprintf('%s/_images/symfony-logo.png', $buildConfig->getOutputDir())));
        $this->assertTrue($filesystem->exists(sprintf('%s/book.html', $buildConfig->getOutputDir())));

        $finder = new Finder();
        $finder->in($buildConfig->getOutputDir())
            ->files()
            ->name('*.html');
        $this->assertCount(1, $finder);

        $indenter = new Indenter();
        $this->assertSame(
            $indenter->indent(file_get_contents(sprintf('%s/../fixtures/expected/build-pdf/book.html', __DIR__))),
            $indenter->indent(file_get_contents(sprintf('%s/book.html', $buildConfig->getOutputDir())))
        );

        $this->assertStringContainsString('[OK] Build complete', $output);
    }

    private function executeCommand(BuildConfig $buildConfig, array $input): string
    {
        $input['--no-theme'] = true;
        $command = new BuildDocsCommand($buildConfig);
        $commandTester = new CommandTester($command);
        $commandTester->execute($input);

        return $commandTester->getDisplay();
    }

    private function createBuildConfig(): BuildConfig
    {
        return (new BuildConfig())
            ->setSymfonyVersion('4.0')
            ->setOutputDir(__DIR__.'/../_output');
    }
}
