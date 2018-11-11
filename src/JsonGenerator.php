<?php declare(strict_types=1);

namespace SymfonyDocs;

use Doctrine\RST\HTML\Document;
use Doctrine\RST\HTML\Environment;
use Doctrine\RST\MetaEntry;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;

/**
 * Class JsonGenerator
 */
class JsonGenerator
{
    /** @var Environment[] */
    private $environments;

    public function __construct(array $documents)
    {
        $this->environments = array_map(
            function (Document $document) {
                return $document->getEnvironment();
            },
            $documents
        );
    }

    public function generateJson(string $inputDir, string $outputDir, ProgressBar $progressBar)
    {
        $finder = new Finder();
        $finder->in($inputDir)
            ->name('*.html')
            ->files();

        $fs = new Filesystem();
        $fs->remove($outputDir);

        foreach ($finder as $file) {
            $crawler = new Crawler($file->getContents());

            $parserFilename = $this->getParserFilename($file->getRealPath(), $inputDir);
            $meta           = $this->getMeta($parserFilename);

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
                str_replace([$inputDir, '.html'], [$outputDir, '.json'], $file->getRealPath()),
                json_encode($data, JSON_PRETTY_PRINT)
            );

            $progressBar->advance();
        }

        $progressBar->finish();
    }

    private function getParserFilename(string $filePath, string $inputDir): string
    {
        return $parserFilename = str_replace([$inputDir.'/', '.html'], ['', ''], $filePath);
    }

    private function getEnvironment(string $parserFilename): Environment
    {
        if (!isset($this->environments[$parserFilename])) {
            throw new \LogicException(sprintf('Cannot find environment for file "%s"', $parserFilename));
        }

        return $this->environments[$parserFilename];
    }

    private function getMeta(string $parserFilename): MetaEntry
    {
        $environment = $this->getEnvironment($parserFilename);

        $allMetas = $environment->getMetas()->getAll();

        if (!isset($allMetas[$parserFilename])) {
            throw new \LogicException(sprintf('Cannot find metas for file "%s"', $parserFilename));
        }

        return $allMetas[$parserFilename];
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
        list($toc, $indexCurrentFile) = $this->getNextPrevInformation($parserFilename);

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
            return [null, null, null];
        }

        $metaParent = $this->getMeta($parentFile);

        if (!$metaParent->getTocs() || \count($metaParent->getTocs()) !== 1) {
            return [null, null, null];
        }

        $toc = current($metaParent->getTocs());

        if (\count($toc) < 2 || !isset(array_flip($toc)[$parserFilename])) {
            return [null, null, null];
        }

        $indexCurrentFile = array_flip($toc)[$parserFilename];

        return [$toc, $indexCurrentFile];
    }
}
