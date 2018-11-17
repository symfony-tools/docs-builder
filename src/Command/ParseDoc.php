<?php declare(strict_types=1);

namespace SymfonyDocs\Command;

use Doctrine\RST\Builder;
use Doctrine\RST\Event\PostBuildRenderEvent;
use Doctrine\RST\Event\PostNodeRenderEvent;
use Doctrine\RST\Event\PostParseDocumentEvent;
use Doctrine\RST\Event\PreBuildParseEvent;
use Doctrine\RST\Event\PreBuildRenderEvent;
use Doctrine\RST\Event\PreBuildScanEvent;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;
use SymfonyDocs\JsonGenerator;
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
            ->addOption('source-dir', null, InputOption::VALUE_REQUIRED, 'RST files Source directory', __DIR__.'/../../..')
            ->addOption('html-output-dir', null, InputOption::VALUE_REQUIRED, 'HTML files output directory', __DIR__.'/../../html')
            ->addOption('json-output-dir', null, InputOption::VALUE_REQUIRED, 'JSON files output directory', __DIR__.'/../../json');
    }

    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        $this->io = new SymfonyStyle($input, $output);
        $this->output = $output;

        $this->sourceDir = $this->getRealAbsolutePath($input->getOption('source-dir'));
        if (!$this->filesystem->exists($this->sourceDir)) {
            throw new \InvalidArgumentException(sprintf('RST source directory "%s" does not exist', $this->sourceDir));
        }

        $this->htmlOutputDir = $this->getRealAbsolutePath($input->getOption('html-output-dir'));
        if ($this->filesystem->exists($this->htmlOutputDir)) {
            $this->filesystem->remove($this->htmlOutputDir);
        }

        $this->jsonOutputDir = $this->getRealAbsolutePath($input->getOption('json-output-dir'));
        if ($this->filesystem->exists($this->jsonOutputDir)) {
            $this->filesystem->remove($this->jsonOutputDir);
        }

        $this->builder = new Builder(KernelFactory::createKernel());
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
        $this->finder->in($input->getOption('source-dir'))
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

        $this->io->note('Start transforming doc into json files');
        $this->progressBar = new ProgressBar($output, $this->finder->count());
        $jsonGenerator     = new JsonGenerator($this->builder->getDocuments()->getAll());
        $jsonGenerator->generateJson($this->htmlOutputDir, $this->jsonOutputDir, $this->progressBar);
        $this->io->newLine(2);
        $this->io->success('Parse process complete');
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
            $this->parsedFiles[] = $postParseDocumentEvent->getDocumentNode()->getEnvironment()->getCurrentFileName();
            $this->progressBar->advance();
        }
    }

    public function preBuildRender()
    {
        $eventManager  = $this->builder->getConfiguration()->getEventManager();
        $eventManager->removeEventListener(
            [PostParseDocumentEvent::POST_PARSE_DOCUMENT],
            $this
        );

        $this->progressBar->finish();

        $this->io->newLine(2);
        $this->io->note('Start rendering in HTML...');
    }
}
