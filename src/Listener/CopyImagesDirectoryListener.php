<?php declare(strict_types=1);

namespace SymfonyDocsBuilder\Listener;

use Symfony\Component\Filesystem\Filesystem;
use SymfonyDocsBuilder\ParameterBag;

final class CopyImagesDirectoryListener
{
    /** @var ParameterBag */
    private $parameterBag;

    public function __construct(ParameterBag $parameterBag)
    {
        $this->parameterBag = $parameterBag;
    }

    public function postBuildRender()
    {
        $fs = new Filesystem();
        if ($fs->exists($imageDir = sprintf('%s/_images', $this->parameterBag->get('sourceDir')))) {
            $fs->mirror($imageDir, sprintf('%s/_images', $this->parameterBag->get('htmlOutputDir')));
        }
    }
}
