<?php

declare(strict_types=1);

/*
 * This file is part of the Docs Builder package.
 * (c) Ryan Weaver <ryan@symfonycasts.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SymfonyDocsBuilder\CI;

use Symfony\Component\Filesystem\Filesystem;
use SymfonyDocsBuilder\BuildContext;
use function Symfony\Component\String\u;

class MissingFilesChecker
{
    private $filesystem;
    private $buildContext;

    public function __construct(BuildContext $buildContext)
    {
        $this->filesystem = new Filesystem();
        $this->buildContext = $buildContext;
    }

    public function getMissingFiles(): array
    {
        $finder = $this->buildContext->createFileFinder();

        $orphanedFiles = [];

        foreach ($finder as $file) {
            $sourcePath = u($file->getPathname())->after($this->buildContext->getSourceDir())->trimEnd('/');
            $htmlFile = sprintf('%s/%s.html', $this->buildContext->getOutputDir(), $sourcePath->slice(0, -4));

            $firstLine = fgets(fopen($file->getRealPath(), 'rb'));
            if (!$this->filesystem->exists($htmlFile) && ':orphan:' !== trim($firstLine)) {
                $orphanedFiles[] = $htmlFile;
            }
        }

        return $orphanedFiles;
    }
}
