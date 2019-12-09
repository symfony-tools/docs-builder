<?php declare(strict_types=1);

namespace SymfonyDocsBuilder\CI;

use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;
use SymfonyDocsBuilder\BuildContext;

class MissingFilesChecker
{
    private $finder;
    private $filesystem;
    private $buildContext;

    public function __construct(BuildContext $buildContext)
    {
        $this->finder       = new Finder();
        $this->filesystem   = new Filesystem();
        $this->buildContext = $buildContext;
    }

    public function getMissingFiles(): array
    {
        $this->finder->in($this->buildContext->getSourceDir())
            ->exclude(['_build', '.github', '.platform', '_images'])
            ->notName('*.rst.inc')
            ->files()
            ->name('*.rst');

        $orphanedFiles = [];

        foreach ($this->finder as $file) {
            $sourcePath = ltrim(substr($file->getPathname(), strlen($this->buildContext->getSourceDir())), '/');

            $htmlFile = sprintf(
                '%s/%s.html',
                $this->buildContext->getOutputDir(),
                substr($sourcePath, 0, strlen($sourcePath) - 4)
            );

            $firstLine = fgets(fopen($file->getRealPath(), 'r'));
            if (!$this->filesystem->exists($htmlFile) && ':orphan:' !== trim($firstLine)) {
                $orphanedFiles[] = $htmlFile;
            }
        }

        return $orphanedFiles;
    }
}
