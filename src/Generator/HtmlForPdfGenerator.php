<?php declare(strict_types=1);

namespace SymfonyDocsBuilder\Generator;

use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;
use SymfonyDocsBuilder\BuildContext;

/**
 * Class HtmlForPdfGenerator
 */
class HtmlForPdfGenerator
{
    use GeneratorTrait;

    public function generateHtmlForPdf(
        array $documents,
        BuildContext $buildContext
    ) {
        $environments = $this->extractEnvironments($documents);

        $finder = new Finder();
        $finder->in($buildContext->getHtmlOutputDir())
            ->depth(0)
            ->notName($buildContext->getParseOnly());

        $fs = new Filesystem();
        foreach ($finder as $file) {
            $fs->remove($file->getRealPath());
        }

        $basePath  = sprintf('%s/%s', $buildContext->getHtmlOutputDir(), $buildContext->getParseOnly());
        $indexFile = sprintf('%s/%s', $basePath, 'index.html');
        if (!$fs->exists($indexFile)) {
            throw new \InvalidArgumentException('File "%s" does not exist', $indexFile);
        }

        $parserFilename = $this->getParserFilename($indexFile, $buildContext->getHtmlOutputDir());
        $meta           = $this->getMeta($environments, $parserFilename);
        dump(current($meta->getTocs()));
    }
}
