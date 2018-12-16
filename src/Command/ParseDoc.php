<?php declare(strict_types=1);

namespace SymfonyDocs\Command;

use Doctrine\RST\Builder;
use Doctrine\RST\Event\PostNodeRenderEvent;
use Doctrine\RST\Event\PostParseDocumentEvent;
use Doctrine\RST\Event\PreBuildRenderEvent;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;
use SymfonyDocs\Generator\HtmlForPdfGenerator;
use SymfonyDocs\Generator\JsonGenerator;
use SymfonyDocs\KernelFactory;

/**
 * Class ParseDoc
 */
class ParseDoc extends Command
{
    protected static $defaultName = 'symfony-docs:parse';

    /** @var SymfonyStyle */
    private $io;
    private $filesystem;
    private $finder;
    /** @var ProgressBar */
    private $progressBar;
    /** @var Builder */
    private $builder;
    /** @var OutputInterface */
    private $output;
    private $sourceDir;
    private $htmlOutputDir;
    private $jsonOutputDir;
    private $parsedFiles = [];
    private $parseOnly;

    public function __construct()
    {
        parent::__construct(self::$defaultName);

        $this->filesystem = new Filesystem();
        $this->finder     = new Finder();
    }

    protected function configure()
    {
        parent::configure();

        $this
            ->addArgument('source-dir', null, InputArgument::REQUIRED, 'RST files Source directory')
            ->addArgument('output-dir', null, InputArgument::OPTIONAL, 'HTML files output directory')
            ->addOption('parse-only', null, InputOption::VALUE_OPTIONAL, 'Parse only given directory for PDF (directory relative from source-dir)', null);
    }

    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        $this->io     = new SymfonyStyle($input, $output);
        $this->output = $output;

        $this->sourceDir = rtrim($this->getRealAbsolutePath($input->getArgument('source-dir')), '/');
        if (!$this->filesystem->exists($this->sourceDir)) {
            throw new \InvalidArgumentException(sprintf('RST source directory "%s" does not exist', $this->sourceDir));
        }

        $outputDir = $input->getArgument('output-dir') ?? $this->sourceDir . '/html';
        $this->htmlOutputDir = rtrim($this->getRealAbsolutePath($outputDir), '/');
        if ($this->filesystem->exists($this->htmlOutputDir)) {
            $this->filesystem->remove($this->htmlOutputDir);
        }

        $this->jsonOutputDir = $this->getRealAbsolutePath($outputDir.'/json');
        if ($this->filesystem->exists($this->jsonOutputDir)) {
            $this->filesystem->remove($this->jsonOutputDir);
        }

        if ($this->parseOnly = trim($input->getOption('parse-only') ?? '', '/')) {
            $absoluteParseOnly = sprintf(
                '%s/%s',
                $this->sourceDir,
                $this->parseOnly
            );

            if (!$this->filesystem->exists($absoluteParseOnly) || !is_dir($absoluteParseOnly)) {
                throw new \InvalidArgumentException(sprintf('Given "parse-only" directory "%s" does not exist', $this->parseOnly));
            }
        }

        $this->builder = new Builder(KernelFactory::createKernel($this->parseOnly));
        $eventManager  = $this->builder->getConfiguration()->getEventManager();
        $eventManager->addEventListener(
            [PostParseDocumentEvent::POST_PARSE_DOCUMENT],
            $this
        );
        $eventManager->addEventListener(
            [PreBuildRenderEvent::PRE_BUILD_RENDER],
            $this
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->finder->in($input->getArgument('source-dir'))
            ->exclude(['_build', '.github', '.platform', '_images'])
            ->notName('*.rst.inc')
            ->name('*.rst');

        $this->io->note(sprintf('Start parsing %d rst files', $this->finder->count()));
        $this->progressBar = new ProgressBar($output, $this->finder->count());

        $this->builder->build(
            $this->sourceDir,
            $this->htmlOutputDir
        );

        $this->io->newLine(2);
        $this->io->success('HTML rendering complete!');

        foreach ($this->finder as $file) {
            $htmlFile = str_replace([$this->sourceDir, '.rst'], [$this->htmlOutputDir, '.html'], $file->getRealPath());
            if (!$this->filesystem->exists($htmlFile)) {
                $this->io->warning(sprintf('Missing file "%s"', $htmlFile));
            }
        }

        if (!$this->parseOnly) {
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
        $jsonGenerator     = new JsonGenerator($this->builder->getDocuments()->getAll());
        $jsonGenerator->generateJson($this->htmlOutputDir, $this->jsonOutputDir, $this->progressBar);
    }

    private function renderDocForPDF()
    {
        $htmlForPdfGenerator = new HtmlForPdfGenerator($this->builder->getDocuments()->getAll());
        $htmlForPdfGenerator->generateHtmlForPdf($this->htmlOutputDir, $this->parseOnly);
    }

    public function handleProgressBar()
    {
        $this->progressBar->advance();
    }

    private function getRealAbsolutePath(string $path): string
    {
        return sprintf(
            '/%s',
            rtrim(
                $this->filesystem->makePathRelative($path, '/'),
                '/'
            )
        );
    }

    public function postParseDocument(PostParseDocumentEvent $postParseDocumentEvent)
    {
        $file = $postParseDocumentEvent->getDocumentNode()->getEnvironment()->getCurrentFileName();
        if (!\in_array($file, $this->parsedFiles)) {
            $this->parsedFiles[] = $file;
            $this->progressBar->advance();
        }
    }

    public function preBuildRender()
    {
        $eventManager = $this->builder->getConfiguration()->getEventManager();
        $eventManager->removeEventListener(
            [PostParseDocumentEvent::POST_PARSE_DOCUMENT],
            $this
        );

        $this->progressBar->finish();

        $this->progressBar = new ProgressBar($this->output);

        $eventManager->addEventListener(
            [PostNodeRenderEvent::POST_NODE_RENDER],
            $this
        );

        $this->io->newLine(2);
        $this->io->note('Start rendering in HTML...');
    }

    public function postNodeRender()
    {
        $this->progressBar->advance();
    }
}
