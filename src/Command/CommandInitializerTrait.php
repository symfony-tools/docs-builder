<?php declare(strict_types=1);

namespace SymfonyDocsBuilder\Command;

use Doctrine\Common\EventManager;
use Doctrine\RST\Builder;
use Doctrine\RST\Event\PostNodeRenderEvent;
use Doctrine\RST\Event\PostParseDocumentEvent;
use Doctrine\RST\Event\PreBuildParseEvent;
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
    /** @var InputInterface */
    private $input;
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
        $this->input  = $input;
        $this->output = $output;

        $this->buildContext->initializeRuntimeConfig(
            $sourceDir,
            $this->initializeHtmlOutputDir($this->filesystem, $outputDir),
            $this->initializeJsonOutputDir($outputDir),
            $this->initializeParseSubPath($input, $sourceDir),
            $this->isCacheDisabled()
        );

        $this->builder = new Builder(
            KernelFactory::createKernel($this->buildContext, $this->urlChecker ?? null)
        );

        $this->eventManager = $this->builder->getConfiguration()->getEventManager();

        $this->initializeProgressBarEventListeners();
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
        if ($this->isCacheDisabled() && $filesystem->exists($htmlOutputDir)) {
            $filesystem->remove($htmlOutputDir);
        }

        return $htmlOutputDir;
    }

    private function initializeParseSubPath(InputInterface $input, string $sourceDir): string
    {
        if (!$input->hasOption('parse-sub-path')) {
            return '';
        }

        if ($parseSubPath = trim($input->getOption('parse-sub-path'), '/')) {
            $absoluteParseSubPath = sprintf(
                '%s/%s',
                $sourceDir,
                $parseSubPath
            );

            if (!$this->filesystem->exists($absoluteParseSubPath) || !is_dir($absoluteParseSubPath)) {
                throw new \InvalidArgumentException(sprintf('Given "parse-sub-path" directory "%s" does not exist', $parseSubPath));
            }
        }

        return $parseSubPath;
    }

    private function initializeJsonOutputDir(string $outputDir): string
    {
        $jsonOutputDir = $this->getRealAbsolutePath($outputDir.'/_json', $this->filesystem);
        if ($this->isCacheDisabled() && $this->filesystem->exists($jsonOutputDir)) {
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

    private function initializeProgressBarEventListeners(): void
    {
        // sets up the "parsing" progress bar
        $this->eventManager->addEventListener(
            [PreBuildParseEvent::PRE_BUILD_PARSE],
            $this
        );

        // advances "parsing" progress bar
        $this->eventManager->addEventListener(
            [PostParseDocumentEvent::POST_PARSE_DOCUMENT],
            $this
        );

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
            $this->buildContext->getHtmlOutputDir()
        );
    }

    /**
     * Removes all existing html files in the output dir that should not exist
     * because previous build in the same output directory has been executed on another version
     */
    private function sanitizeOutputDirs(Finder $finder)
    {
        $rstFiles = array_map(
            function (string $rstFile) {
                return str_replace([$this->buildContext->getSourceDir(), '.rst'], '', $rstFile);
            },
            array_keys(iterator_to_array($finder))
        );

        $this->sanitizeOutputDir($rstFiles, $this->buildContext->getHtmlOutputDir(), 'html');
        $this->sanitizeOutputDir($rstFiles, $this->buildContext->getJsonOutputDir(), 'json');
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

    private function isCacheDisabled(): bool
    {
        return $this->input->hasOption('disable-cache') && (bool) $this->input->getOption('disable-cache');
    }

    /**
     * Called very early: used to initialize the "parsing" progress bar.
     *
     * @param PreBuildParseEvent $event
     */
    public function preBuildParse(PreBuildParseEvent $event)
    {
        $parseQueue = $event->getParseQueue();
        $parseCount = count($parseQueue->getAllFilesThatRequireParsing());
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
