<?php declare(strict_types=1);

namespace SymfonyDocsBuilder\Generator;

use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;

/**
 * Class HtmlForPdfGenerator
 */
class HtmlForPdfGenerator
{
    use GeneratorTrait;

    public function generateHtmlForPdf(
        array $documents,
        string $htmlDir,
        string $parseOnly/*, ProgressBar $progressBar*/
    )
    {
        $environments = $this->extractEnvironments($documents);

        $finder = new Finder();
        $finder->in($htmlDir)
            ->depth(0)
            ->notName($parseOnly);

        $fs = new Filesystem();
        foreach ($finder as $file) {
            $fs->remove($file->getRealPath());
        }

        $basePath  = sprintf('%s/%s', $htmlDir, $parseOnly);
        $indexFile = sprintf('%s/%s', $basePath, 'index.html');
        if (!$fs->exists($indexFile)) {
            throw new \InvalidArgumentException('File "%s" does not exist', $indexFile);
        }

        $parserFilename = $this->getParserFilename($indexFile, $htmlDir);
        $meta           = $this->getMeta($environments, $parserFilename);
        dump(current($meta->getTocs()));
    }
}
