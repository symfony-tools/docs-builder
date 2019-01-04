<?php declare(strict_types=1);

namespace SymfonyDocsBuilder\Listener;

use Symfony\Component\Filesystem\Filesystem;
use SymfonyDocsBuilder\ConfigBag;

class CopyImagesDirectoryListener
{
    private $configBag;

    public function __construct(ConfigBag $configBag)
    {
        $this->configBag = $configBag;
    }

    public function postBuildRender()
    {
        $fs = new Filesystem();
        if ($fs->exists($imageDir = sprintf('%s/_images', $this->configBag->getSourceDir()))) {
            $fs->mirror($imageDir, sprintf('%s/_images', $this->configBag->getHtmlOutputDir()));
        }
    }
}
