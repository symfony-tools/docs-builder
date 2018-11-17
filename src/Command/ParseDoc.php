<?php declare(strict_types=1);

namespace SymfonyDocs\Command;

use Doctrine\RST\Builder;
use Doctrine\RST\Configuration;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;
use SymfonyDocs\KernelFactory;
use SymfonyDocs\SymfonyDocConfiguration;
use SymfonyDocs\JsonGenerator;

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
    private $sourceDir;
    private $htmlOutputDir;
    private $jsonOutputDir;

    public function __construct()
    {
        parent::__construct(self::$defaultName);

        $this->filesystem = new Filesystem();
        $this->finder = new Finder();
    }

    protected function configure()
    {
        parent::configure();

        $this
            ->addOption('source-dir', null, InputOption::VALUE_REQUIRED, 'RST files Source directory', __DIR__.'/../../..')
            ->addOption('html-output-dir', null, InputOption::VALUE_REQUIRED, 'HTML files output directory', __DIR__.'/../../html')
            ->addOption('json-output-dir', null, InputOption::VALUE_REQUIRED, 'JSON files output directory', __DIR__.'/../../json')
        ;
    }

    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        $this->io = new SymfonyStyle($input, $output);

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
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $builder = KernelFactory::createKernel();

//        $builder->addHook([$this, 'handleProgressBar']);

        $this->finder->in($input->getOption('source-dir'))
            ->exclude(['_build', '.github', '.platform', '_images'])
            ->notName('*.rst.inc')
            ->name('*.rst');

        $this->io->note(sprintf('Start parsing into html %d rst files', $this->finder->count()));
        $this->progressBar = new ProgressBar($output, $this->finder->count());

        $builder->build(
            $this->sourceDir,
            $this->htmlOutputDir
        );

        $this->progressBar->finish();
        $this->io->newLine(2);
        $this->io->success('Parse into html complete');

        foreach ($this->finder as $file) {
            $htmlFile = str_replace([$this->sourceDir, '.rst'], [$this->htmlOutputDir, '.html'], $file->getRealPath());
            if (!$this->filesystem->exists($htmlFile)) {
                $this->io->warning(sprintf('Missing file "%s"', $htmlFile));
            }
        }

        $this->io->note('Start transforming doc into json files');
        $this->progressBar = new ProgressBar($output, $this->finder->count());
        $jsonGenerator = new JsonGenerator($builder->getDocuments()->getAll());
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
}
