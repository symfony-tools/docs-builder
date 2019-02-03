<?php declare(strict_types=1);

namespace SymfonyDocsBuilder\Generator;

use Doctrine\RST\Environment;
use Doctrine\RST\Meta\MetaEntry;
use Doctrine\RST\Meta\Metas;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;
use SymfonyDocsBuilder\BuildContext;

/**
 * Class JsonGenerator
 */
class JsonGenerator
{
    private $metas;

    private $buildContext;

    public function __construct(Metas $metas, BuildContext $buildContext)
    {
        $this->metas = $metas;
        $this->buildContext = $buildContext;
    }

    public function generateJson(ProgressBar $progressBar)
    {
        $fs = new Filesystem();

        foreach ($this->metas->getAll() as $filename => $metaEntry) {
            $parserFilename = $filename;
            $jsonFilename   = $this->buildContext->getJsonOutputDir().'/'.$filename.'.json';

            $crawler = new Crawler(file_get_contents($this->buildContext->getHtmlOutputDir().'/'.$filename.'.html'));

            $data = [
                'body'              => $crawler->filter('body')->html(),
                'title'             => $metaEntry->getTitle(),
                'current_page_name' => $parserFilename,
                'toc'               => $this->generateToc($metaEntry, current($metaEntry->getTitles())[1]),
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
        $meta       = $this->getMetaEntry($parserFilename);
        $parentFile = $meta->getParent();

        // if current file is an index, next is the first chapter
        if ('index' === $parentFile && \count($tocs = $meta->getTocs()) === 1 && \count($tocs[0]) > 0) {
            return [
                'title' => $this->getMetaEntry($tocs[0][0])->getTitle(),
                'link'  => $this->getMetaEntry($tocs[0][0])->getUrl(),
            ];
        }

        list($toc, $indexCurrentFile) = $this->getNextPrevInformation($parserFilename);

        if (!isset($toc[$indexCurrentFile + 1])) {
            return null;
        }

        $nextFileName = $toc[$indexCurrentFile + 1];

        return [
            'title' => $this->getMetaEntry($nextFileName)->getTitle(),
            'link'  => $this->getMetaEntry($nextFileName)->getUrl(),
        ];
    }

    private function guessPrev(string $parserFilename): ?array
    {
        $meta       = $this->getMetaEntry($parserFilename);
        $parentFile = $meta->getParent();

        // no prev if parent is an index
        if ('index' === $parentFile) {
            return null;
        }

        list($toc, $indexCurrentFile) = $this->getNextPrevInformation($parserFilename);

        // if current file is the first one of the chapter, prev is the direct parent
        if (0 === $indexCurrentFile) {
            return [
                'title' => $this->getMetaEntry($parentFile)->getTitle(),
                'link'  => $this->getMetaEntry($parentFile)->getUrl(),
            ];
        }

        if (!isset($toc[$indexCurrentFile - 1])) {
            return null;
        }

        $prevFileName = $toc[$indexCurrentFile - 1];

        return [
            'title' => $this->getMetaEntry($prevFileName)->getTitle(),
            'link'  => $this->getMetaEntry($prevFileName)->getUrl(),
        ];
    }

    private function getNextPrevInformation(string $parserFilename): ?array
    {
        $meta       = $this->getMetaEntry($parserFilename);
        $parentFile = $meta->getParent();

        if (!$parentFile) {
            return [null, null];
        }

        $metaParent = $this->getMetaEntry($parentFile);

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

    private function getMetaEntry(string $parserFilename): MetaEntry
    {
        $metaEntry = $this->metas->get($parserFilename);

        if (null === $metaEntry) {
            throw new \LogicException(sprintf('Could not find MetaEntry for file "%s"', $parserFilename));
        }

        return $metaEntry;
    }
}
