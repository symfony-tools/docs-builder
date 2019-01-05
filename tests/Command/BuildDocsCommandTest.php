<?php declare(strict_types=1);

namespace App\Tests\Command;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\Filesystem\Filesystem;
use SymfonyDocsBuilder\BuildContext;
use SymfonyDocsBuilder\Command\BuildDocsCommand;

class BuildDocsCommandTest extends TestCase
{
    public function testBuildDocs()
    {
        $buildContext = $this->createParameterBag();
        $outputDir    = sprintf('%s/tests/_output', $buildContext->getBasePath());

        $fs = new Filesystem();
        if ($fs->exists($outputDir)) {
            $fs->remove($outputDir);
        }

        $command       = new BuildDocsCommand($buildContext);
        $commandTester = new CommandTester($command);
        $commandTester->execute(
            [
                'source-dir' => sprintf('%s/tests/fixtures/source/main', $buildContext->getBasePath()),
                'output-dir' => $outputDir,
            ]
        );

        $output = $commandTester->getDisplay();
        $this->assertNotContains('[WARNING]', $output);
        $this->assertContains('[OK] Parse process complete', $output);
    }

    private function createParameterBag(): BuildContext
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