<?php declare(strict_types=1);

namespace SymfonyDocsBuilder\Generator;

use Symfony\Component\DomCrawler\Crawler;
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

        // extracting all files from index's TOC, in the right order
        $parserFilename = $this->getParserFilename($indexFile, $buildContext->getHtmlOutputDir());
        $meta           = $this->getMeta($environments, $parserFilename);
        $files          = current($meta->getTocs());

        $htmlDir = $buildContext->getHtmlOutputDir();
        $files   = array_map(
            function ($file) use ($htmlDir, $fs) {
                $file = sprintf('%s/%s.html', $htmlDir, $file);
                if (!$fs->exists($file)) {
                    throw new \LogicException('File "%s" does not exist', $file);
                }

                return $file;
            },
            $files
        );
        array_unshift($files, $indexFile);

        // building one big html file with all contents
        $fileContent = '';
        foreach ($files as $file) {
            $crawler = new Crawler(file_get_contents($file));

            $fileContent .= "\n";
            $fileContent .= $crawler->filter('body')->html();
        }

        $fileContent = sprintf(
            '<html><head><title>%s</title></head><body>%s</body></html>',
            $buildContext->getParseOnly(),
            $fileContent
        );

        $filename = sprintf('%s/%s.html', $htmlDir, $buildContext->getParseOnly());
        file_put_contents($filename, $fileContent);

        $fs->remove($basePath);
    }
}
