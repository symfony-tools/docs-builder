<?php declare(strict_types=1);

namespace SymfonyDocsBuilder\Listener;

use Symfony\Component\Filesystem\Filesystem;

final class CopyImagesDirectoryListener
{
    private $sourceDir;
    private $htmlOutputDir;

    public function __construct(string $sourceDir, string $htmlOutputDir)
    {
        $this->sourceDir     = $sourceDir;
        $this->htmlOutputDir = $htmlOutputDir;
    }

    public function postBuildRender()
    {
        $fs = new Filesystem();
        if ($fs->exists($imageDir = sprintf('%s/_images', $this->sourceDir))) {
            $fs->mirror($imageDir, sprintf('%s/_images', $this->htmlOutputDir));
        }
    }
}
