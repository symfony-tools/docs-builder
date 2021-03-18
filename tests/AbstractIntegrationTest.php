<?php

namespace SymfonyDocsBuilder\Tests;

use Doctrine\RST\Builder;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Filesystem\Filesystem;
use SymfonyDocsBuilder\BuildConfig;

class AbstractIntegrationTest extends TestCase
{
    protected function createBuildConfig(string $sourceDir): BuildConfig
    {
        return (new BuildConfig())
            ->setSymfonyVersion('4.0')
            ->setContentDir($sourceDir)
            ->disableBuildCache()
            ->setOutputDir(__DIR__.'/_output')
        ;
    }

    /**
     * @after
     */
    public function cleanUpOutput()
    {
        $filesystem = new Filesystem();
        $filesystem->remove(__DIR__.'/_output');
    }
}
