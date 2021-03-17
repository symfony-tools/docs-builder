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
use SymfonyDocsBuilder\BuildConfig;
use function Symfony\Component\String\u;

class MissingFilesChecker
{
    private $filesystem;
    private $buildConfig;

    public function __construct(BuildConfig $buildConfig)
    {
        $this->filesystem = new Filesystem();
        $this->buildConfig = $buildConfig;
    }

    public function getMissingFiles(): array
    {
        $finder = $this->buildConfig->createFileFinder();

        $orphanedFiles = [];

        foreach ($finder as $file) {
            $sourcePath = u($file->getPathname())->after($this->buildConfig->getContentDir())->trimStart('/');
            $htmlFile = sprintf('%s/%s.html', $this->buildConfig->getOutputDir(), $sourcePath->slice(0, -4));

            $firstLine = fgets(fopen($file->getRealPath(), 'rb'));
            if (!$this->filesystem->exists($htmlFile) && ':orphan:' !== trim($firstLine)) {
                $orphanedFiles[] = $htmlFile;
            }
        }

        return $orphanedFiles;
    }
}
