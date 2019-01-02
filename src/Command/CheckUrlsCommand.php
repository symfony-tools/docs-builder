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
use SymfonyDocs\CI\UrlChecker;
use SymfonyDocs\KernelFactory;

class CheckUrlsCommand extends Command
{
    protected static $defaultName = 'build:check-urls';

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
    private $parsedFiles = [];
    private $urlChecker;

    public function __construct()
    {
        parent::__construct(self::$defaultName);

        $this->filesystem = new Filesystem();
        $this->finder     = new Finder();
        $this->urlChecker = new UrlChecker();
    }

    protected function configure()
    {
        parent::configure();

        $this
            ->addArgument('source-dir', InputArgument::OPTIONAL, 'RST files Source directory', getcwd())
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

        $this->htmlOutputDir = rtrim($this->getRealAbsolutePath(sprintf('%s/../../var/urls-checker', __DIR__)), '/');
        if ($this->filesystem->exists($this->htmlOutputDir)) {
            $this->filesystem->remove($this->htmlOutputDir);
        }

        $this->builder = new Builder(
            KernelFactory::createKernel($this->sourceDir, $this->htmlOutputDir, null, $this->urlChecker)
        );

        $eventManager = $this->builder->getConfiguration()->getEventManager();
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

        if ($this->urlChecker->getInvalidUrls()) {
            $this->io->warning('Some urls are invalid in the docs!');
            $this->io->table(['url', 'status code'], $this->urlChecker->getInvalidUrls());
        } else {
            $this->io->success('All urls in the docs are valid!');
        }

        $this->filesystem->remove($this->htmlOutputDir);
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
    }

    public function postNodeRender()
    {
        $this->progressBar->advance();
    }
}
