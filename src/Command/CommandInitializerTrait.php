<?php declare(strict_types=1);

namespace SymfonyDocsBuilder\Command;

use Doctrine\Common\EventManager;
use Doctrine\RST\Builder;
use Doctrine\RST\Event\PostNodeRenderEvent;
use Doctrine\RST\Event\PostParseDocumentEvent;
use Doctrine\RST\Event\PreBuildRenderEvent;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;
use SymfonyDocsBuilder\BuildContext;
use SymfonyDocsBuilder\KernelFactory;

trait CommandInitializerTrait
{
    /** @var BuildContext */
    private $buildContext;
    /** @var SymfonyStyle */
    private $io;
    /** @var OutputInterface */
    private $output;
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
        $this->io     = new SymfonyStyle($input, $output);
        $this->output = $output;

        $this->buildContext->initializeRuntimeConfig(
            $sourceDir,
            $this->initializeHtmlOutputDir($this->filesystem, $outputDir),
            $this->initializeJsonOutputDir($outputDir),
            $this->initializeParseOnly($input, $sourceDir)
        );

        $this->builder = new Builder(
            KernelFactory::createKernel($this->buildContext, $this->urlChecker ?? null)
        );

        $this->eventManager = $this->builder->getConfiguration()->getEventManager();

        $this->initializeEventListeners();
    }

    private function initializeSourceDir(InputInterface $input, Filesystem $filesystem): string
    {
        $sourceDir = rtrim($this->getRealAbsolutePath($input->getArgument('source-dir'), $filesystem), '/');
        if (!$filesystem->exists($sourceDir)) {
            throw new \InvalidArgumentException(sprintf('RST source directory "%s" does not exist', $sourceDir));
        }

        return $sourceDir;
    }

    private function initializeHtmlOutputDir(Filesystem $filesystem, string $path): string
    {
        $htmlOutputDir = rtrim($this->getRealAbsolutePath($path, $filesystem), '/');
        if ($filesystem->exists($htmlOutputDir)) {
            $filesystem->remove($htmlOutputDir);
        }

        return $htmlOutputDir;
    }

    private function initializeParseOnly(InputInterface $input, string $sourceDir): string
    {
        if (!$input->hasOption('parse-only')) {
            return '';
        }

        if ($parseOnly = trim($input->getOption('parse-only'), '/')) {
            $absoluteParseOnly = sprintf(
                '%s/%s',
                $sourceDir,
                $parseOnly
            );

            if (!$this->filesystem->exists($absoluteParseOnly) || !is_dir($absoluteParseOnly)) {
                throw new \InvalidArgumentException(sprintf('Given "parse-only" directory "%s" does not exist', $parseOnly));
            }
        }

        return $parseOnly;
    }

    private function initializeJsonOutputDir(string $outputDir): string
    {
        $jsonOutputDir = $this->getRealAbsolutePath($outputDir.'/json', $this->filesystem);
        if ($this->filesystem->exists($jsonOutputDir)) {
            $this->filesystem->remove($jsonOutputDir);
        }

        return $jsonOutputDir;
    }

    private function getRealAbsolutePath(string $path, Filesystem $filesystem): string
    {
        return sprintf(
            '/%s',
            rtrim(
                $filesystem->makePathRelative($path, '/'),
                '/'
            )
        );
    }

    private function initializeEventListeners(): void
    {
        $this->eventManager->addEventListener(
            [PostParseDocumentEvent::POST_PARSE_DOCUMENT],
            $this
        );
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

        $this->io->note(sprintf('Start parsing %d rst files', $this->finder->count()));
        $this->progressBar = new ProgressBar($this->output, $this->finder->count());

        $this->builder->build(
            $this->buildContext->getSourceDir(),
            $this->buildContext->getHtmlOutputDir()
        );
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
        $this->eventManager->removeEventListener(
            [PostParseDocumentEvent::POST_PARSE_DOCUMENT],
            $this
        );

        $this->progressBar->finish();

        $this->progressBar = new ProgressBar($this->output);

        $this->eventManager->addEventListener(
            [PostNodeRenderEvent::POST_NODE_RENDER],
            $this
        );

        $this->io->newLine(2);
    }

    public function postNodeRender(): void
    {
        $this->progressBar->advance();
    }
}