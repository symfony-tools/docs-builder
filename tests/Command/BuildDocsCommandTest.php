<?php declare(strict_types=1);

namespace App\Tests\Command;

use Gajus\Dindent\Indenter;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;
use SymfonyDocsBuilder\BuildContext;
use SymfonyDocsBuilder\Command\BuildDocsCommand;

class BuildDocsCommandTest extends TestCase
{
    public function testBuildDocs()
    {
        $buildContext = $this->createBuildContext();
        $outputDir    = sprintf('%s/tests/_output', $buildContext->getBasePath());

        $output = $this->executeCommand(
            $buildContext,
            [
                'source-dir' => sprintf('%s/tests/fixtures/source/main', $buildContext->getBasePath()),
                'output-dir' => $outputDir,
            ]
        );

        $this->assertContains('[OK] Parse process complete (0 files were loaded from cache)', $output);

        $filesystem = new Filesystem();
        $this->assertTrue($filesystem->exists(sprintf('%s/_images/symfony-logo.png', $outputDir)));

        $output = $this->executeCommand(
            $buildContext,
            [
                'source-dir' => sprintf('%s/tests/fixtures/source/main', $buildContext->getBasePath()),
                'output-dir' => $outputDir,
            ]
        );
        $this->assertContains('[OK] Parse process complete (3 files were loaded from cache)', $output);
    }

    public function testBuildDocsForPdf()
    {
        $buildContext = $this->createBuildContext();
        $outputDir    = sprintf('%s/tests/_output', $buildContext->getBasePath());

        $fs = new Filesystem();
        if ($fs->exists($outputDir)) {
            $fs->remove($outputDir);
        }

        $output = $this->executeCommand(
            $buildContext,
            [
                'source-dir'       => sprintf('%s/tests/fixtures/source/build-pdf', $buildContext->getBasePath()),
                'output-dir'       => $outputDir,
                '--parse-sub-path' => 'book',
            ]
        );

        $filesystem = new Filesystem();
        $this->assertTrue($filesystem->exists(sprintf('%s/_images/symfony-logo.png', $outputDir)));
        $this->assertTrue($filesystem->exists(sprintf('%s/book.html', $outputDir)));

        $finder = new Finder();
        $finder->in($outputDir)
            ->files()
            ->name('*.html');
        $this->assertCount(1, $finder);

        $indenter = new Indenter();
        $this->assertSame(
            $indenter->indent(file_get_contents(sprintf('%s/../fixtures/expected/build-pdf/book.html', __DIR__))),
            $indenter->indent(file_get_contents(sprintf('%s/book.html', $outputDir)))
        );

        $this->assertContains('[OK] Parse process complete', $output);
    }

    private function executeCommand(BuildContext $buildContext, array $input): string
    {
        $command       = new BuildDocsCommand($buildContext);
        $commandTester = new CommandTester($command);
        $commandTester->execute($input);

        return $commandTester->getDisplay();
    }

    private function createBuildContext(): BuildContext
    {
        $buildContext = new BuildContext(
            realpath(__DIR__.'/../..'),
            '4.0',
            'https://api.symfony.com/4.0',
            'https://secure.php.net/manual/en',
            'https://symfony.com/doc/4.0'
        );

        return $buildContext;
    }
}