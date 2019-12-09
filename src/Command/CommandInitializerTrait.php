<?php

declare(strict_types=1);

/*
 * This file is part of the Docs Builder package.
 * (c) Ryan Weaver <ryan@symfonycasts.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SymfonyDocsBuilder\Command;

use Doctrine\Common\EventManager;
use Doctrine\RST\Builder;
use Doctrine\RST\Event\PostParseDocumentEvent;
use Doctrine\RST\Event\PreBuildParseEvent;
use Doctrine\RST\Event\PreBuildRenderEvent;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;
use SymfonyDocsBuilder\BuildContext;

trait CommandInitializerTrait
{
    /** @var BuildContext */
    private $buildContext;
    /** @var Builder */
    private $builder;
    /** @var Filesystem */
    private $filesystem;
    /** @var Finder */
    private $finder;
    /** @var ProgressBar */
    private $progressBar;
    /** @var EventManager */
    private $eventManager;

    private $parsedFiles = [];

    private function doInitialize(InputInterface $input, OutputInterface $output, string $sourceDir, string $outputDir)
    {
        $this->eventManager = $this->builder->getConfiguration()->getEventManager();

        $this->initializeProgressBarEventListeners();
    }

    private function initializeProgressBarEventListeners(): void
    {
        // tries to handle progress bar for "rendering"
        $this->eventManager->addEventListener(
            [PreBuildRenderEvent::PRE_BUILD_RENDER],
            $this
        );
    }

    private function startBuild()
    {
        $this->finder->in($this->buildContext->getSourceDir())
            ->exclude(['_build', '.github', '.platform', '_images'])
            ->notName('*.rst.inc')
            ->name('*.rst');

        $this->sanitizeOutputDirs($this->finder);

        $this->builder->build(
            $this->buildContext->getSourceDir(),
            $this->buildContext->getOutputDir()
        );
    }

    /**
     * Removes all existing html files in the output dir that should not exist
     * because previous build in the same output directory has been executed on another version.
     */
    private function sanitizeOutputDirs(Finder $finder)
    {
        $rstFiles = array_map(
            function (string $rstFile) {
                return str_replace([$this->buildContext->getSourceDir(), '.rst'], '', $rstFile);
            },
            array_keys(iterator_to_array($finder))
        );

        $this->sanitizeOutputDir($rstFiles, $this->buildContext->getOutputDir(), 'html');
        $this->sanitizeOutputDir($rstFiles, $this->buildContext->getOutputDir(), 'fjson');
    }

    private function sanitizeOutputDir(array $existingRstFiles, string $outputDir, string $format)
    {
        if (!$this->filesystem->exists($outputDir)) {
            return;
        }

        $htmlFinder = new Finder();
        $htmlFinder->in($outputDir)
            ->name('*.html');

        $htmlFiles = array_map(
            function (string $htmlFile) use ($outputDir, $format) {
                return str_replace([$outputDir, '.'.$format], '', $htmlFile);
            },
            array_keys(iterator_to_array($htmlFinder))
        );

        $filesNotExistingInCurrentVersion = array_map(
            function ($file) use ($outputDir, $format) {
                return sprintf('%s%s.%s', $outputDir, $file, $format);
            },
            array_values(array_diff($htmlFiles, $existingRstFiles))
        );

        foreach ($filesNotExistingInCurrentVersion as $file) {
            $this->filesystem->remove($file);
        }
    }

    /**
     * Called very early: used to initialize the "parsing" progress bar.
     *
     * @param PreBuildParseEvent $event
     */
    public function preBuildParse(PreBuildParseEvent $event)
    {
        $parseQueue = $event->getParseQueue();
        $parseCount = \count($parseQueue->getAllFilesThatRequireParsing());
        $this->io->note(sprintf('Start parsing %d out-of-date rst files', $parseCount));
        $this->progressBar = new ProgressBar($this->output, $parseCount);
    }

    public function postParseDocument(PostParseDocumentEvent $postParseDocumentEvent): void
    {
        $file = $postParseDocumentEvent->getDocumentNode()->getEnvironment()->getCurrentFileName();
        if (!\in_array($file, $this->parsedFiles)) {
            $this->parsedFiles[] = $file;
            $this->progressBar->advance();
        }
    }

    public function doPreBuildRender()
    {
        // finishes the "parse" progress bar
        $this->progressBar->finish();

        $this->io->newLine(2);
        // TODO: create a proper progress bar for rendering
    }
}
