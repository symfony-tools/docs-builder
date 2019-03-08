<?php declare(strict_types=1);

namespace SymfonyDocsBuilder\Command;

use Doctrine\RST\Event\PostBuildRenderEvent;
use Doctrine\RST\Meta\CachedMetasLoader;
use Doctrine\RST\Meta\Metas;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;
use SymfonyDocsBuilder\BuildContext;
use SymfonyDocsBuilder\CI\MissingFilesChecker;
use SymfonyDocsBuilder\Generator\HtmlForPdfGenerator;
use SymfonyDocsBuilder\Generator\JsonGenerator;
use SymfonyDocsBuilder\Listener\CopyImagesDirectoryListener;

class BuildDocsCommand extends Command
{
    use CommandInitializerTrait;

    protected static $defaultName = 'build:docs';

    private $missingFilesChecker;

    public function __construct(BuildContext $buildContext)
    {
        parent::__construct(self::$defaultName);

        $this->filesystem   = new Filesystem();
        $this->finder       = new Finder();
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
        ;
    }

    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        if ($input->getOption('parse-sub-path') && $input->getOption('output-json')) {
            throw new \InvalidArgumentException(sprintf('Cannot pass both --parse-sub-path and --output-json options.'));
        }


        $sourceDir = $this->initializeSourceDir($input, $this->filesystem);
        $outputDir = $input->getArgument('output-dir') ?? $sourceDir.'/html';

        $this->doInitialize($input, $output, $sourceDir, $outputDir);

        $this->builder->getConfiguration()->getEventManager()->addEventListener(
            PostBuildRenderEvent::POST_BUILD_RENDER,
            new CopyImagesDirectoryListener($this->buildContext)
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->startBuild();
        $buildErrors = $this->builder->getErrorManager()->getErrors();

        $this->io->success('HTML rendering complete!');

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

            file_put_contents($logPath, implode("\n", $buildErrors));
        }

        $metas = $this->getMetas();
        if ($this->buildContext->getParseSubPath()) {
            $this->renderDocForPDF($metas);
        } elseif ($input->getOption('output-json')) {
            $this->generateJson($metas);
        }

        $this->io->newLine(2);

        $successMessage = 'Parse process complete';

        if (!$this->buildContext->getDisableCache()) {
            $successMessage = sprintf(
                '%s (%d files were loaded from cache)',
                $successMessage,
                $this->finder->count() - count($this->builder->getDocuments()->getAll())
            );
        }
        $this->io->success($successMessage);
    }

    private function generateJson(Metas $metas)
    {
        $this->io->note('Start exporting doc into json files');
        $this->progressBar = new ProgressBar($this->output, $this->finder->count());

        $jsonGenerator = new JsonGenerator($metas, $this->buildContext);
        $jsonGenerator->setOutput($this->io);
        $jsonGenerator->generateJson($this->progressBar);
    }

    private function renderDocForPDF(Metas $metas)
    {
        $htmlForPdfGenerator = new HtmlForPdfGenerator($metas, $this->buildContext);
        $htmlForPdfGenerator->generateHtmlForPdf();
    }

    public function preBuildRender()
    {
        $this->doPreBuildRender();

        $this->io->note('Start rendering in HTML...');
    }

    private function getMetas(): Metas
    {
        return $this->builder->getMetas();
    }
}
