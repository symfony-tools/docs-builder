<?php declare(strict_types=1);

namespace SymfonyDocsBuilder\Generator;

use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;
use SymfonyDocsBuilder\BuildContext;

class HtmlForPdfGenerator
{
    use GeneratorTrait;

    public function generateHtmlForPdf(array $documents) {
        $this->extractEnvironmentsAndCachedMetas($documents);

        $finder = new Finder();
        $finder->in($this->buildContext->getHtmlOutputDir())
            ->depth(0)
            ->notName([$this->buildContext->getParseSubPath(), '_images']);

        $fs = new Filesystem();
        foreach ($finder as $file) {
            $fs->remove($file->getRealPath());
        }

        $basePath  = sprintf('%s/%s', $this->buildContext->getHtmlOutputDir(), $this->buildContext->getParseSubPath());
        $indexFile = sprintf('%s/%s', $basePath, 'index.html');
        if (!$fs->exists($indexFile)) {
            throw new \InvalidArgumentException(sprintf('File "%s" does not exist', $indexFile));
        }

        // extracting all files from index's TOC, in the right order
        $parserFilename = $this->getParserFilename($indexFile, $this->buildContext->getHtmlOutputDir());
        $meta           = $this->getMeta($parserFilename);
        $files          = current($meta->getTocs());
        array_unshift($files, sprintf('%s/index', $this->buildContext->getParseSubPath()));

        // building one big html file with all contents
        $content = '';
        $htmlDir = $this->buildContext->getHtmlOutputDir();
        $relativeImagesPath = str_repeat('../', substr_count($this->buildContext->getParseSubPath(), '/'));
        foreach ($files as $file) {
            $meta = $this->getMeta($file);

            $filename = sprintf('%s/%s.html', $htmlDir, $file);
            if (!$fs->exists($filename)) {
                throw new \LogicException(sprintf('File "%s" does not exist', $filename));
            }

            // extract <body> content
            $crawler     = new Crawler(file_get_contents($filename));
            $fileContent = $crawler->filter('body')->html();

            $dir = dirname($meta->getFile());
            $fileContent = $this->fixInternalUrls($fileContent, $dir);

            $fileContent = $this->fixInternalImages($fileContent, $relativeImagesPath);

            $uid = str_replace('/', '-', $meta->getFile());
            $fileContent = $this->fixUniqueIdsAndAnchors($fileContent, $uid);

            $content .= "\n";
            $content .= sprintf('<div id="%s">%s</div>', $uid, $fileContent);
        }

        $content = sprintf(
            '<html><head><title>%s</title></head><body>%s</body></html>',
            $this->buildContext->getParseSubPath(),
            $content
        );

        $content = $this->cleanupContent($content);

        $filename = sprintf('%s/%s.html', $htmlDir, $this->buildContext->getParseSubPath());
        file_put_contents($filename, $content);
        $fs->remove($basePath);
    }

    private function fixInternalUrls(string $fileContent, string $dir): string
    {
        return preg_replace_callback(
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
    }

    private function fixInternalImages(string $fileContent, string $relativeImagesPath): string
    {
        return $fileContent = preg_replace('{src="(?:\.\./)+([^"]+?)"}', "src=\"$relativeImagesPath$1\"", $fileContent);
    }

    private function fixUniqueIdsAndAnchors(string $fileContent, string $uid): string
    {
        return preg_replace_callback(
            '/id="([^"]+)"/',
            function ($matches) use ($uid) {
                return sprintf('id="%s-%s"', $uid, $matches[1]);
            },
            $fileContent
        );
    }

    private function cleanupContent($content)
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
