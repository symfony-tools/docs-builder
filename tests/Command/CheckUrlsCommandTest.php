<?php declare(strict_types=1);

namespace App\Tests\Command;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\Filesystem\Filesystem;
use SymfonyDocsBuilder\BuildContext;
use SymfonyDocsBuilder\Command\CheckUrlsCommand;

class CheckUrlsCommandTest extends TestCase
{
    public function testBuildDocs()
    {
        $buildContext = $this->createParameterBag();
        $outputDir    = sprintf('%s/tests/_output', $buildContext->getBasePath());

        $fs = new Filesystem();
        if ($fs->exists($outputDir)) {
            $fs->remove($outputDir);
        }

        $command       = new CheckUrlsCommand($buildContext);
        $commandTester = new CommandTester($command);
        $commandTester->execute(
            [
                'source-dir' => sprintf('%s/tests/fixtures/source/main', $buildContext->getBasePath()),
            ]
        );

        $output = $commandTester->getDisplay();
        $this->assertContains('[WARNING] Some urls are invalid in the docs!', $output);
        $this->assertContains('https://symfony.com/404   404', $output);
        $this->assertContains('http://invalid-url        0', $output);
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