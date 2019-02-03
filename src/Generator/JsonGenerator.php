<?php declare(strict_types=1);

namespace SymfonyDocsBuilder\Generator;

use Doctrine\RST\Environment;
use Doctrine\RST\Meta\MetaEntry;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;

/**
 * Class JsonGenerator
 */
class JsonGenerator
{
    use GeneratorTrait;

    public function generateJson(array $documents, ProgressBar $progressBar)
    {
        $this->extractEnvironmentsAndCachedMetas($documents);

        $finder = new Finder();
        $finder->in($this->buildContext->getHtmlOutputDir())
            ->name('*.html')
            ->files();

        $fs = new Filesystem();

        foreach ($finder as $file) {
            $parserFilename = $this->getParserFilename($file->getRealPath(), $this->buildContext->getHtmlOutputDir());
            $jsonFilename   = str_replace([$this->buildContext->getHtmlOutputDir(), '.html'], [$this->buildContext->getJsonOutputDir(), '.json'], $file->getRealPath());

            if ($this->useCacheForFile($parserFilename)) {
                if (!file_exists($jsonFilename)) {
                    throw new \RuntimeException(
                        sprintf('File %s does not exist although cache is enabled and related environment is not available', $jsonFilename)
                    );
                }

                continue;
            }

            $meta = $this->getMeta($parserFilename);

            $crawler = new Crawler($file->getContents());

            $data = [
                'body'              => $crawler->filter('body')->html(),
                'title'             => $meta->getTitle(),
                'current_page_name' => $parserFilename,
                'toc'               => $this->generateToc($meta, current($meta->getTitles())[1]),
                'next'              => $this->guessNext($parserFilename),
                'prev'              => $this->guessPrev($parserFilename),
                'rellinks'          => [
                    $this->guessNext($parserFilename),
                    $this->guessPrev($parserFilename),
                ],
            ];

            $fs->dumpFile(
                $jsonFilename,
                json_encode($data, JSON_PRETTY_PRINT)
            );

            $progressBar->advance();
        }

        $progressBar->finish();
    }

    private function generateToc(MetaEntry $metaEntry, ?array $titles): array
    {
        if (null === $titles) {
            return [];
        }

        $tocTree = [];

        foreach ($titles as $title) {
            $tocTree[] = [
                'url'      => sprintf('%s#%s', $metaEntry->getUrl(), Environment::slugify($title[0])),
                'title'    => $title[0],
                'children' => $this->generateToc($metaEntry, $title[1]),
            ];
        }

        return $tocTree;
    }

    private function guessNext(string $parserFilename): ?array
    {
        $meta       = $this->getMeta($parserFilename);
        $parentFile = $meta->getParent();

        // if current file is an index, next is the first chapter
        if ('index' === $parentFile && \count($tocs = $meta->getTocs()) === 1 && \count($tocs[0]) > 0) {
            return [
                'title' => $this->getMeta($tocs[0][0])->getTitle(),
                'link'  => $this->getMeta($tocs[0][0])->getUrl(),
            ];
        }

        list($toc, $indexCurrentFile) = $this->getNextPrevInformation($parserFilename);

        if (!isset($toc[$indexCurrentFile + 1])) {
            return null;
        }

        $nextFileName = $toc[$indexCurrentFile + 1];

        return [
            'title' => $this->getMeta($nextFileName)->getTitle(),
            'link'  => $this->getMeta($nextFileName)->getUrl(),
        ];
    }

    private function guessPrev(string $parserFilename): ?array
    {
        $meta       = $this->getMeta($parserFilename);
        $parentFile = $meta->getParent();

        // no prev if parent is an index
        if ('index' === $parentFile) {
            return null;
        }

        list($toc, $indexCurrentFile) = $this->getNextPrevInformation($parserFilename);

        // if current file is the first one of the chapter, prev is the direct parent
        if (0 === $indexCurrentFile) {
            return [
                'title' => $this->getMeta($parentFile)->getTitle(),
                'link'  => $this->getMeta($parentFile)->getUrl(),
            ];
        }

        if (!isset($toc[$indexCurrentFile - 1])) {
            return null;
        }

        $prevFileName = $toc[$indexCurrentFile - 1];

        return [
            'title' => $this->getMeta($prevFileName)->getTitle(),
            'link'  => $this->getMeta($prevFileName)->getUrl(),
        ];
    }

    private function getNextPrevInformation(string $parserFilename): ?array
    {
        $meta       = $this->getMeta($parserFilename);
        $parentFile = $meta->getParent();

        if (!$parentFile) {
            return [null, null];
        }

        $metaParent = $this->getMeta($parentFile);

        if (!$metaParent->getTocs() || \count($metaParent->getTocs()) !== 1) {
            return [null, null];
        }

        $toc = current($metaParent->getTocs());

        if (\count($toc) < 2 || !isset(array_flip($toc)[$parserFilename])) {
            return [null, null];
        }

        $indexCurrentFile = array_flip($toc)[$parserFilename];

        return [$toc, $indexCurrentFile];
    }
}
