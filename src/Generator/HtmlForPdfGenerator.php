<?php

declare(strict_types=1);

/*
 * This file is part of the Docs Builder package.
 * (c) Ryan Weaver <ryan@symfonycasts.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SymfonyDocsBuilder\Generator;

use Doctrine\RST\Meta\MetaEntry;
use Doctrine\RST\Meta\Metas;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;
use SymfonyDocsBuilder\BuildConfig;
use function Symfony\Component\String\u;

class HtmlForPdfGenerator
{
    private $metas;

    private $buildConfig;

    public function __construct(Metas $metas, BuildConfig $buildConfig)
    {
        $this->metas = $metas;
        $this->buildConfig = $buildConfig;
    }

    public function generateHtmlForPdf()
    {
        $finder = new Finder();
        $finder->in($this->buildConfig->getOutputDir())
            ->depth(0)
            ->notName([$this->buildConfig->getSubdirectoryToBuild(), '_images']);

        $fs = new Filesystem();
        foreach ($finder as $file) {
            $fs->remove($file->getRealPath());
        }

        $basePath = sprintf('%s/%s', $this->buildConfig->getOutputDir(), $this->buildConfig->getSubdirectoryToBuild());
        $indexFile = sprintf('%s/%s', $basePath, 'index.html');
        if (!$fs->exists($indexFile)) {
            throw new \InvalidArgumentException(sprintf('File "%s" does not exist', $indexFile));
        }

        // extracting all files from index's TOC, in the right order
        $parserFilename = $this->getParserFilename($indexFile, $this->buildConfig->getOutputDir());
        $meta = $this->getMetaEntry($parserFilename);
        $files = current($meta->getTocs());
        array_unshift($files, sprintf('%s/index', $this->buildConfig->getSubdirectoryToBuild()));

        // building one big html file with all contents
        $content = '';
        $htmlDir = $this->buildConfig->getOutputDir();
        $relativeImagesPath = str_repeat('../', substr_count($this->buildConfig->getSubdirectoryToBuild(), '/'));
        foreach ($files as $file) {
            $meta = $this->getMetaEntry($file);

            $filename = sprintf('%s/%s.html', $htmlDir, $file);
            if (!$fs->exists($filename)) {
                throw new \LogicException(sprintf('File "%s" does not exist', $filename));
            }

            // extract <body> content
            $crawler = new Crawler(file_get_contents($filename));
            $fileContent = $crawler->filter('body')->html();

            $dir = \dirname($meta->getFile());
            $fileContent = $this->fixInternalUrls($fileContent, $dir);

            $fileContent = $this->fixInternalImages($fileContent, $relativeImagesPath);

            $uid = str_replace('/', '-', $meta->getFile());
            $fileContent = $this->fixUniqueIdsAndAnchors($fileContent, $uid);

            $content .= "\n";
            $content .= sprintf('<div id="%s">%s</div>', $uid, $fileContent);
        }

        $content = sprintf(
            '<html><head><title>%s</title></head><body>%s</body></html>',
            $this->buildConfig->getSubdirectoryToBuild(),
            $content
        );

        $content = $this->cleanupContent($content);

        $filename = sprintf('%s/%s.html', $htmlDir, $this->buildConfig->getSubdirectoryToBuild());
        file_put_contents($filename, $content);
        $fs->remove($basePath);
    }

    private function fixInternalUrls(string $fileContent, string $dir): string
    {
        return preg_replace_callback(
            '/href="([^"]+?)"/',
            static function ($matches) use ($dir) {
                if (u($matches[1])->startsWith(['http', '#'])) {
                    return $matches[0];
                }

                $path = [];
                foreach (u($matches[1])->replace('.html', '')->replace('#', '-')->split('/') as $urlPart) {
                    $part = $urlPart->toString();
                    if ('..' === $part) {
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
            static function ($matches) use ($uid) {
                return sprintf('id="%s-%s"', $uid, $matches[1]);
            },
            $fileContent
        );
    }

    private function cleanupContent(string $content): string
    {
        // remove internal anchors
        $content = preg_replace('#<a class="headerlink"([^>]+)>(.*)</a>#', '$2', $content);

        // convert links to footnote
        $content = preg_replace_callback(
            '#<a href="(.*?)" class="reference external"(?:[^>]*)>(.*?)</a>#',
            static function ($matches): string {
                if (u($matches[2])->startsWith('http')) {
                    return sprintf('<em><a href="%s">%s</a></em>', $matches[2], $matches[2]);
                }

                return sprintf('<em>%s</em><span class="footnote"><code>%s</code></span>', $matches[2], $matches[1]);
            },
            $content
        );

        return $content;
    }

    private function getMetaEntry(string $parserFilename): MetaEntry
    {
        $metaEntry = $this->metas->get($parserFilename);

        if (null === $metaEntry) {
            throw new \LogicException(sprintf('Could not find MetaEntry for file "%s"', $parserFilename));
        }

        return $metaEntry;
    }

    private function getParserFilename(string $filePath, string $inputDir): string
    {
        return $parserFilename = str_replace([$inputDir.'/', '.html'], ['', ''], $filePath);
    }
}
