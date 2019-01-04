<?php declare(strict_types=1);

namespace SymfonyDocsBuilder\Generator;

use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;
use SymfonyDocsBuilder\ConfigBag;

/**
 * Class HtmlForPdfGenerator
 */
class HtmlForPdfGenerator
{
    use GeneratorTrait;

    public function generateHtmlForPdf(
        array $documents,
        ConfigBag $configBag
    ) {
        $environments = $this->extractEnvironments($documents);

        $finder = new Finder();
        $finder->in($configBag->getHtmlOutputDir())
            ->depth(0)
            ->notName($configBag->getParseOnly());

        $fs = new Filesystem();
        foreach ($finder as $file) {
            $fs->remove($file->getRealPath());
        }

        $basePath  = sprintf('%s/%s', $configBag->getHtmlOutputDir(), $configBag->getParseOnly());
        $indexFile = sprintf('%s/%s', $basePath, 'index.html');
        if (!$fs->exists($indexFile)) {
            throw new \InvalidArgumentException('File "%s" does not exist', $indexFile);
        }

        $parserFilename = $this->getParserFilename($indexFile, $configBag->getHtmlOutputDir());
        $meta           = $this->getMeta($environments, $parserFilename);
        dump(current($meta->getTocs()));
    }
}
