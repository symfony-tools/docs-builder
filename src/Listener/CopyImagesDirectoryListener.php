<?php declare(strict_types=1);

namespace SymfonyDocsBuilder\Listener;

use Symfony\Component\Filesystem\Filesystem;
use SymfonyDocsBuilder\BuildContext;

class CopyImagesDirectoryListener
{
    private $buildContext;

    public function __construct(BuildContext $buildContext)
    {
        $this->buildContext = $buildContext;
    }

    public function postBuildRender()
    {
        $fs = new Filesystem();
        if ($fs->exists($imageDir = sprintf('%s/_images', $this->buildContext->getSourceDir()))) {
            $fs->mirror($imageDir, sprintf('%s/_images', $this->buildContext->getHtmlOutputDir()));
        }
    }
}
