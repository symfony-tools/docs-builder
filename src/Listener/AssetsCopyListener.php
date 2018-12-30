<?php declare(strict_types=1);

namespace SymfonyDocsBuilder\Listener;

use Symfony\Component\Filesystem\Filesystem;

final class AssetsCopyListener
{
    /** @var string */
    private $targetDir;

    public function __construct(string $targetDir)
    {
        $this->targetDir = $targetDir;
    }

    public function postBuildRender()
    {
        $fs = new Filesystem();
        $fs->mirror(
            sprintf('%s/../Templates/default/assets', __DIR__),
            sprintf('%s/assets', $this->targetDir)
        );
    }
}
