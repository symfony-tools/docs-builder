<?php declare(strict_types=1);

namespace SymfonyDocsBuilder\Command;

use Doctrine\Common\EventManager;
use Doctrine\RST\Builder;
use Doctrine\RST\Configuration;
use Doctrine\RST\Event\PostBuildRenderEvent;
use Doctrine\RST\Meta\Metas;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Filesystem\Filesystem;
use SymfonyDocsBuilder\BuildContext;
use SymfonyDocsBuilder\CI\MissingFilesChecker;
use SymfonyDocsBuilder\Generator\HtmlForPdfGenerator;
use SymfonyDocsBuilder\Generator\JsonGenerator;
use SymfonyDocsBuilder\KernelFactory;
use SymfonyDocsBuilder\Listener\AssetsCopyListener;
use SymfonyDocsBuilder\Listener\BuildProgressListener;
use SymfonyDocsBuilder\Listener\CopyImagesDirectoryListener;

class BuildDocsCommand extends Command
{
    protected static $defaultName = 'build:docs';

    private $buildContext;
    private $missingFilesChecker;
    /** @var SymfonyStyle */
    private $io;

    public function __construct(BuildContext $buildContext)
    {
        parent::__construct(self::$defaultName);

        $this->buildContext = $buildContext;
        $this->missingFilesChecker = new MissingFilesChecker($buildContext);
    }

    protected function configure()
    {
        parent::configure();

        $this
            ->addArgument('source-dir', InputArgument::OPTIONAL, 'RST files Source directory', getcwd())
            ->addArgument('output-dir', InputArgument::OPTIONAL, 'HTML files output directory')
            ->addOption(
                'parse-sub-path',
                null,
                InputOption::VALUE_OPTIONAL,
                'Parse only given sub directory and combine it into a single file (directory relative from source-dir)',
                ''
            )
            ->addOption(
                'output-json',
                null,
                InputOption::VALUE_NONE,
                'If provided, .fjson metadata files will be written'
            )
            ->addOption(
                'disable-cache',
                null,
                InputOption::VALUE_NONE,
                'If provided, caching meta will be disabled'
            )
            ->addOption(
                'save-errors',
                null,
                InputOption::VALUE_REQUIRED,
                'Path where any errors should be saved'
            )
            ->addOption(
                'no-theme',
                null,
                InputOption::VALUE_NONE,
                'Use the default theme instead of the styled one'
            )
        ;
    }

    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        $this->io = new SymfonyStyle($input, $output);

        $sourceDir = $input->getArgument('source-dir');
        if (!file_exists($sourceDir)) {
            throw new \InvalidArgumentException(sprintf('RST source directory "%s" does not exist', $sourceDir));
        }

        $filesystem = new Filesystem();
        $htmlOutputDir = $input->getArgument('output-dir') ?? $sourceDir.'/html';
        if ($input->getOption('disable-cache') && $filesystem->exists($htmlOutputDir)) {
            $filesystem->remove($htmlOutputDir);
        }

        $parseSubPath = $input->getOption('parse-sub-path');
        if ($parseSubPath && $input->getOption('output-json')) {
            throw new \InvalidArgumentException('Cannot pass both --parse-sub-path and --output-json options.');
        }

        if (!file_exists($sourceDir.'/'.$parseSubPath)) {
            throw new \InvalidArgumentException(sprintf('Given "parse-sub-path" directory "%s" does not exist', $parseSubPath));
        }

        $this->buildContext->initializeRuntimeConfig(
            $sourceDir,
            $htmlOutputDir,
            $parseSubPath,
            $input->getOption('disable-cache'),
            $input->getOption('no-theme') ? Configuration::THEME_DEFAULT : 'rtd'
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $builder = new Builder(
            KernelFactory::createKernel($this->buildContext, $this->urlChecker ?? null)
        );

        $this->initializeListeners($builder->getConfiguration()->getEventManager());

        $builder->build(
            $this->buildContext->getSourceDir(),
            $this->buildContext->getOutputDir()
        );

        $buildErrors = $builder->getErrorManager()->getErrors();

        $missingFiles = $this->missingFilesChecker->getMissingFiles();
        foreach ($missingFiles as $missingFile) {
            $message = sprintf('Missing file "%s"', $missingFile);
            $buildErrors[] = $message;
            $this->io->warning($message);
        }

        if ($logPath = $input->getOption('save-errors')) {
            if (count($buildErrors) > 0) {
                array_unshift($buildErrors, sprintf('Build errors from "%s"', date('Y-m-d h:i:s')));
            }

            $filesystem = new Filesystem();
            $filesystem->dumpFile($logPath, implode("\n", $buildErrors));
        }

        $metas = $builder->getMetas();
        if ($this->buildContext->getParseSubPath()) {
            $this->renderDocForPDF($metas);
        } elseif ($input->getOption('output-json')) {
            $this->generateJson($metas);
        }

        $this->io->newLine(2);

        $successMessage = 'Build complete!';
        $this->io->success($successMessage);
    }

    private function generateJson(Metas $metas)
    {
        $this->io->note('Start exporting doc into json files');

        $jsonGenerator = new JsonGenerator($metas, $this->buildContext);
        $jsonGenerator->setOutput($this->io);
        $jsonGenerator->generateJson();
    }

    private function renderDocForPDF(Metas $metas)
    {
        $htmlForPdfGenerator = new HtmlForPdfGenerator($metas, $this->buildContext);
        $htmlForPdfGenerator->generateHtmlForPdf();
    }

    private function initializeListeners(EventManager $eventManager)
    {
        $eventManager->addEventListener(
            PostBuildRenderEvent::POST_BUILD_RENDER,
            new CopyImagesDirectoryListener($this->buildContext)
        );

        if (!$this->buildContext->getParseSubPath()) {
            $eventManager->addEventListener(
                [PostBuildRenderEvent::POST_BUILD_RENDER],
                new AssetsCopyListener($this->buildContext->getOutputDir())
            );
        }

        $progressListener = new BuildProgressListener($this->io);
        $progressListener->attachListeners($eventManager);
    }
}
