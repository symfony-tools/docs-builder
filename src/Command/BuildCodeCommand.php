<?php declare(strict_types=1);

namespace SymfonyDocsBuilder\Command;

use Doctrine\RST\Parser;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use SymfonyDocsBuilder\Code\DocumentCodeBuilder;
use SymfonyDocsBuilder\BuildContext;
use SymfonyDocsBuilder\KernelFactory;

class BuildCodeCommand extends Command
{
    protected static $defaultName = 'build:code';

    private $buildContext;

    public function __construct(BuildContext $buildContext)
    {
        parent::__construct(self::$defaultName);

        $this->buildContext = $buildContext;
    }

    protected function configure()
    {
        parent::configure();

        $this
            ->addArgument('file', InputArgument::REQUIRED, 'The RST file to build')
            ->addArgument('build-dir', InputArgument::OPTIONAL, 'The directory where files will be built into', getcwd().'/built_code')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);

        // a bit of a hack - this must be initialized
        $this->buildContext->initializeRuntimeConfig(
            '',
            ''
        );
        $kernel = KernelFactory::createKernel($this->buildContext);

        $codeBuilder = new DocumentCodeBuilder(
            $kernel,
            $input->getArgument('build-dir')
        );
        $codeBuilder->setConsoleLogger($io);
        $buildableDocument = $codeBuilder->createBuildableDocument($input->getArgument('file'));
        $codeBuilder->buildDocument($buildableDocument);
    }
}
