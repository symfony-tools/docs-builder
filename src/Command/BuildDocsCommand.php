<?php declare(strict_types=1);

namespace SymfonyDocsBuilder\Command;

use Doctrine\RST\Event\PostBuildRenderEvent;
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

    protected static $defaultName = 'symfony-docs-builder:build-docs';

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
            ->addOption('parse-only', null, InputOption::VALUE_OPTIONAL, 'Parse only given directory for PDF (directory relative from source-dir)', '');
    }

    protected function initialize(InputInterface $input, OutputInterface $output)
    {
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

        $this->io->newLine(2);
        $this->io->success('HTML rendering complete!');

        $this->missingFilesChecker->checkMissingFiles($this->io);

        if (!$this->buildContext->getParseOnly()) {
            $this->generateJson();
        } else {
            $this->renderDocForPDF();
        }

        $this->io->newLine(2);
        $this->io->success('Parse process complete');
    }

    private function generateJson()
    {
        $this->io->note('Start exporting doc into json files');
        $this->progressBar = new ProgressBar($this->output, $this->finder->count());

        $jsonGenerator = new JsonGenerator();
        $jsonGenerator->generateJson($this->builder->getDocuments()->getAll(), $this->buildContext, $this->progressBar);
    }

    private function renderDocForPDF()
    {
        $htmlForPdfGenerator = new HtmlForPdfGenerator();
        $htmlForPdfGenerator->generateHtmlForPdf($this->builder->getDocuments()->getAll(), $this->buildContext);
    }

    public function preBuildRender()
    {
        $this->doPreBuildRender();

        $this->io->note('Start rendering in HTML...');
    }
}
