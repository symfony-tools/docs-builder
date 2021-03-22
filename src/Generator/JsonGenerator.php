<?php

declare(strict_types=1);

/*
 * This file is part of the Docs Builder package.
 * (c) Ryan Weaver <ryan@symfonycasts.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SymfonyDocsBuilder\Generator;

use Doctrine\RST\Environment;
use Doctrine\RST\Meta\MetaEntry;
use Doctrine\RST\Meta\Metas;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\Filesystem\Filesystem;
use SymfonyDocsBuilder\BuildConfig;
use function Symfony\Component\String\u;

class JsonGenerator
{
    private $metas;

    private $buildConfig;

    /** @var SymfonyStyle|null */
    private $output;

    public function __construct(Metas $metas, BuildConfig $buildConfig)
    {
        $this->metas = $metas;
        $this->buildConfig = $buildConfig;
    }

    /**
     * Returns an array of each JSON file string, keyed by the input filename
     *
     * @param string $masterDocument The file whose toctree should be read first
     * @return string[]
     */
    public function generateJson(string $masterDocument = 'index'): array
    {
        $fs = new Filesystem();

        $progressBar = new ProgressBar($this->output ?: new NullOutput());
        $progressBar->setMaxSteps(\count($this->metas->getAll()));

        $walkedFiles = [];
        $tocTreeHierarchy = $this->walkTocTreeAndReturnHierarchy(
            $masterDocument,
            $walkedFiles
        );
        // for purposes of prev/next/parents, the "master document"
        // behaves as if it's the first item in the toctree
        $tocTreeHierarchy = [$masterDocument => []] + $tocTreeHierarchy;
        $flattenedTocTree = $this->flattenTocTree($tocTreeHierarchy);

        $fJsonFiles = [];
        foreach ($this->metas->getAll() as $filename => $metaEntry) {
            $parserFilename = $filename;
            $jsonFilename = $this->buildConfig->getOutputDir().'/'.$filename.'.fjson';

            $crawler = new Crawler(file_get_contents($this->buildConfig->getOutputDir().'/'.$filename.'.html'));

            $next = $this->determineNext($parserFilename, $flattenedTocTree, $masterDocument);
            $prev = $this->determinePrev($parserFilename, $flattenedTocTree);
            $data = [
                'title' => $metaEntry->getTitle(),
                'parents' => $this->determineParents($parserFilename, $tocTreeHierarchy) ?: [],
                'current_page_name' => $parserFilename,
                'toc' => $this->generateToc($metaEntry, current($metaEntry->getTitles())[1]),
                'next' => $next,
                'prev' => $prev,
                'body' => $crawler->filter('body')->html(),
            ];

            $fs->dumpFile(
                $jsonFilename,
                json_encode($data, JSON_PRETTY_PRINT)
            );
            $fJsonFiles[$filename] = $data;

            $progressBar->advance();
        }

        $progressBar->finish();

        return $fJsonFiles;
    }

    public function setOutput(SymfonyStyle $output)
    {
        $this->output = $output;
    }

    private function generateToc(MetaEntry $metaEntry, ?array $titles): array
    {
        if (null === $titles) {
            return [];
        }

        $tocTree = [];

        foreach ($titles as $title) {
            $tocTree[] = [
                'url' => sprintf('%s#%s', $metaEntry->getUrl(), Environment::slugify($title[0])),
                'page' => u($metaEntry->getUrl())->beforeLast('.html'),
                'fragment' => Environment::slugify($title[0]),
                'title' => $title[0],
                'children' => $this->generateToc($metaEntry, $title[1]),
            ];
        }

        return $tocTree;
    }

    private function determineNext(string $parserFilename, array $flattenedTocTree): ?array
    {
        $foundCurrentFile = false;
        $nextFileName = null;

        foreach ($flattenedTocTree as $filename) {
            if ($foundCurrentFile) {
                $nextFileName = $filename;

                break;
            }

            if ($filename === $parserFilename) {
                $foundCurrentFile = true;
            }
        }

        // no next document found!
        if (null === $nextFileName) {
            return null;
        }

        return $this->makeRelativeLink($parserFilename, $nextFileName);
    }

    private function determinePrev(string $parserFilename, array $flattenedTocTree): ?array
    {
        $previousFileName = null;
        $foundCurrentFile = false;
        foreach ($flattenedTocTree as $filename) {
            if ($filename === $parserFilename) {
                $foundCurrentFile = true;
                break;
            }

            $previousFileName = $filename;
        }

        // no previous document found!
        if (null === $previousFileName || !$foundCurrentFile) {
            return null;
        }

        return $this->makeRelativeLink($parserFilename, $previousFileName);
    }

    private function getMetaEntry(string $parserFilename, bool $throwOnMissing = false): ?MetaEntry
    {
        $metaEntry = $this->metas->get($parserFilename);

        // this is possible if there are invalid references
        if (null === $metaEntry) {
            $message = sprintf('Could not find MetaEntry for file "%s"', $parserFilename);

            if ($throwOnMissing) {
                throw new \Exception($message);
            }

            if ($this->output) {
                $this->output->note($message);
            }
        }

        return $metaEntry;
    }

    /**
     * Creates a hierarchy of documents by crawling the toctree's
     *
     * This looks at the
     * toc tree of the master document, following the first entry
     * like a link, then repeating the process on the next document's
     * toc tree (if it has one). When it hits a dead end, it would
     * go back to the master document and click the second link.
     * But, it skips any links that have been seen before. This
     * is the logic behind how the prev/next parent information is created.
     *
     * Example result:
     *      [
     *          'dashboards' => [],
     *          'design' => [
     *              'crud' => [],
     *              'design/sub-page' => [],
     *          ],
     *          'fields' => []
     *      ]
     *
     * See the JsonIntegrationTest for a test case.
     */
    private function walkTocTreeAndReturnHierarchy(string $filename, array &$walkedFiles): array
    {
        $hierarchy = [];
        foreach ($this->getMetaEntry($filename)->getTocs() as $toc) {
            foreach ($toc as $tocFilename) {
                // only walk a file one time, the first time you see it
                if (in_array($tocFilename, $walkedFiles, true)) {
                    continue;
                }

                $walkedFiles[] = $tocFilename;

                $hierarchy[$tocFilename] = $this->walkTocTreeAndReturnHierarchy($tocFilename, $walkedFiles);
            }
        }

        return $hierarchy;
    }

    /**
     * Takes the structure from walkTocTreeAndReturnHierarchy() and flattens it.
     *
     * For example:
     *
     *      [dashboards, design, crud, design/sub-page, fields]
     *
     * @return string[]
     */
    private function flattenTocTree(array $tocTreeHierarchy): array
    {
        $files = [];

        foreach ($tocTreeHierarchy as $filename => $tocTree) {
            $files[] = $filename;

            $files = array_merge($files, $this->flattenTocTree($tocTree));
        }

        return $files;
    }

    private function determineParents(string $parserFilename, array $tocTreeHierarchy, array $parents = []): ?array
    {
        foreach ($tocTreeHierarchy as $filename => $tocTree) {
            if ($filename === $parserFilename) {
                return $parents;
            }

            $subParents = $this->determineParents($parserFilename, $tocTree, $parents + [$this->makeRelativeLink($parserFilename, $filename)]);

            if (null !== $subParents) {
                // the item WAS found and the parents were returned
                return $subParents;
            }
        }

        // item was not found
        return null;
    }

    private function makeRelativeLink(string $currentFilename, string $filename): array
    {
        $meta = $this->getMetaEntry($filename);

        return [
            'title' => $meta->getTitle(),
            'link' => str_repeat('../', substr_count($currentFilename, '/')).$meta->getUrl(),
        ];
    }
}
