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
            ->notName([$buildContext->getParseOnly(), '_images']);

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
        array_unshift($files, sprintf('%s/index', $buildContext->getParseOnly()));

        // building one big html file with all contents
        $content = '';
        $htmlDir = $buildContext->getHtmlOutputDir();
        foreach ($files as $file) {
            $meta = $this->getMeta($environments, $file);

            $filename = sprintf('%s/%s.html', $htmlDir, $file);
            if (!$fs->exists($filename)) {
                throw new \LogicException(sprintf('File "%s" does not exist', $filename));
            }

            $crawler     = new Crawler(file_get_contents($filename));
            $fileContent = $crawler->filter('body')->html();

            $uid = str_replace('/', '-', $meta->getFile());
            $dir = dirname($meta->getFile());

            // fix internal URLs
            $fileContent = preg_replace_callback(
                '/href="([^"]+?)"/',
                function ($matches) use ($dir) {
                    if ('http' === substr($matches[1], 0, 4) || '#' === substr($matches[1], 0, 1)) {
                        return $matches[0];
                    }

                    $path = [];
                    foreach (explode('/', $dir.'/'.str_replace(['.html', '#'], ['', '-'], $matches[1])) as $part) {
                        if ('..' == $part) {
                            array_pop($path);
                        } else {
                            $path[] = $part;
                        }
                    }

                    $path = implode('-', $path);

                    return sprintf('href="#%s"', $path);
                },
                $fileContent
            );

            // fix internal images
            // $page = preg_replace('{src="(?:\.\./)+([^"]+?)"}', "src=\"$relativeImagesPath$1\"", $fileContent);

            // fix # and id references to be unique
            $fileContent = preg_replace_callback(
                '/id="([^"]+)"/',
                function ($matches) use ($uid) {
                    return sprintf('id="%s-%s"', $uid, $matches[1]);
                },
                $fileContent
            );

            $content .= "\n";
            $content .= sprintf('<div id="%s">%s</div>', $uid, $fileContent);
        }

        $content = sprintf(
            '<html><head><title>%s</title></head><body>%s</body></html>',
            $buildContext->getParseOnly(),
            $content
        );

        $content = $this->cleanupContent($content);

        $filename = sprintf('%s/%s.html', $htmlDir, $buildContext->getParseOnly());
        file_put_contents($filename, $content);
        $fs->remove($basePath);
    }

    protected function cleanupContent($content)
    {
        // remove internal anchors
        $content = preg_replace('#<a class="headerlink"([^>]+)>¶</a>#', '', $content);

        // convert links to footnote
        $content = preg_replace_callback(
            '#<a href="(.*?)" class="reference external"(?:[^>]*)>(.*?)</a>#',
            function ($matches) {
                if (0 === strpos($matches[2], 'http')) {
                    return sprintf('<em><a href="%s">%s</a></em>', $matches[2], $matches[2]);
                }

                return sprintf('<em>%s</em><span class="footnote"><code>%s</code></span>', $matches[2], $matches[1]);
            },
            $content
        );

        return $content;
    }
}
