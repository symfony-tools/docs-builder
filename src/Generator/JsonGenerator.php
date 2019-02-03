<?php declare(strict_types=1);

namespace SymfonyDocsBuilder\Generator;

use Doctrine\RST\Environment;
use Doctrine\RST\Meta\MetaEntry;
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
    use GeneratorTrait;

    public function generateJson(
        array $documents,
        BuildContext $buildContext,
        ProgressBar $progressBar
    ) {
        $environments = $this->extractEnvironments($documents);

        $finder = new Finder();
        $finder->in($buildContext->getHtmlOutputDir())
            ->name('*.html')
            ->files();

        $fs = new Filesystem();
        $fs->remove($buildContext->getJsonOutputDir());

        foreach ($finder as $file) {


            $crawler = new Crawler($file->getContents());

            $parserFilename = $this->getParserFilename($file->getRealPath(), $buildContext->getHtmlOutputDir());
            $meta           = $this->getMeta($environments, $parserFilename);

            $data = [
                'body'              => $crawler->filter('body')->html(),
                'title'             => $meta->getTitle(),
                'current_page_name' => $parserFilename,
                'toc'               => $this->generateToc($meta, current($meta->getTitles())[1]),
                'next'              => $this->guessNext($environments, $parserFilename),
                'prev'              => $this->guessPrev($environments, $parserFilename),
                'rellinks'          => [
                    $this->guessNext($environments, $parserFilename),
                    $this->guessPrev($environments, $parserFilename),
                ],
            ];

            $fs->dumpFile(
                str_replace([$buildContext->getHtmlOutputDir(), '.html'], [$buildContext->getJsonOutputDir(), '.json'], $file->getRealPath()),
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

    private function guessNext(array $environments, string $parserFilename): ?array
    {
        $meta       = $this->getMeta($environments, $parserFilename);
        $parentFile = $meta->getParent();

        // if current file is an index, next is the first chapter
        if ('index' === $parentFile && \count($tocs = $meta->getTocs()) === 1 && \count($tocs[0]) > 0) {
            return [
                'title' => $this->getMeta($environments, $tocs[0][0])->getTitle(),
                'link'  => $this->getMeta($environments, $tocs[0][0])->getUrl(),
            ];
        }

        list($toc, $indexCurrentFile) = $this->getNextPrevInformation($environments, $parserFilename);

        if (!isset($toc[$indexCurrentFile + 1])) {
            return null;
        }

        $nextFileName = $toc[$indexCurrentFile + 1];

        return [
            'title' => $this->getMeta($environments, $nextFileName)->getTitle(),
            'link'  => $this->getMeta($environments, $nextFileName)->getUrl(),
        ];
    }

    private function guessPrev(array $environments, string $parserFilename): ?array
    {
        $meta       = $this->getMeta($environments, $parserFilename);
        $parentFile = $meta->getParent();

        // no prev if parent is an index
        if ('index' === $parentFile) {
            return null;
        }

        list($toc, $indexCurrentFile) = $this->getNextPrevInformation($environments, $parserFilename);

        // if current file is the first one of the chapter, prev is the direct parent
        if (0 === $indexCurrentFile) {
            return [
                'title' => $this->getMeta($environments, $parentFile)->getTitle(),
                'link'  => $this->getMeta($environments, $parentFile)->getUrl(),
            ];
        }

        if (!isset($toc[$indexCurrentFile - 1])) {
            return null;
        }

        $prevFileName = $toc[$indexCurrentFile - 1];

        return [
            'title' => $this->getMeta($environments, $prevFileName)->getTitle(),
            'link'  => $this->getMeta($environments, $prevFileName)->getUrl(),
        ];
    }

    private function getNextPrevInformation(array $environments, string $parserFilename): ?array
    {
        $meta       = $this->getMeta($environments, $parserFilename);
        $parentFile = $meta->getParent();

        if (!$parentFile) {
            return [null, null];
        }

        $metaParent = $this->getMeta($environments, $parentFile);

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
