<?php declare(strict_types=1);

namespace SymfonyDocsBuilder\Command;

use Doctrine\Common\EventManager;
use Doctrine\RST\Builder;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;
use SymfonyDocsBuilder\BuildContext;
use SymfonyDocsBuilder\CI\UrlChecker;

class CheckUrlsCommand extends Command
{
    use CommandInitializerTrait;

    protected static $defaultName = 'build:check-urls';

    /** @var SymfonyStyle */
    private $io;
    /** @var Filesystem */
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
    private $buildContext;

    /** @var EventManager */
    private $eventManager;

    public function __construct(BuildContext $buildContext)
    {
        parent::__construct(self::$defaultName);

        $this->filesystem   = new Filesystem();
        $this->finder       = new Finder();
        $this->urlChecker   = new UrlChecker();
        $this->buildContext = $buildContext;
    }

    protected function configure()
    {
        parent::configure();

        $this
            ->addArgument('source-dir', InputArgument::OPTIONAL, 'RST files Source directory', getcwd());
    }

    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        $this->doInitialize(
            $input,
            $output,
            $this->initializeSourceDir($input, $this->filesystem),
            sprintf('%s/../../var/urls-checker', __DIR__)
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->startBuild();

        $this->io->newLine(2);

        if ($this->urlChecker->getInvalidUrls()) {
            $this->io->warning('Some urls are invalid in the docs!');
            $this->io->table(['url', 'status code'], $this->urlChecker->getInvalidUrls());
        } else {
            $this->io->success('All urls in the docs are valid!');
        }

        $this->filesystem->remove($this->htmlOutputDir);
    }

    public function preBuildRender()
    {
        $this->doPreBuildRender();
    }
}
