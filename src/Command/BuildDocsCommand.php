<?php

/*
 * This file is part of the Docs Builder package.
 *
 * (c) Ryan Weaver <ryan@symfonycasts.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SymfonyTools\GuidesExtension\Command;

use Flyfinder\Finder;
use Flyfinder\Path;
use Flyfinder\Specification\InPath;
use League\Flysystem\Adapter\Local;
use League\Flysystem\Filesystem;
use League\Tactician\CommandBus;
use Psr\Log\LoggerInterface;
use SymfonyTools\GuidesExtension\Build\BuildConfig;
use SymfonyTools\GuidesExtension\Build\BuildEnvironment;
use SymfonyTools\GuidesExtension\Build\LocalBuildEnvironment;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use phpDocumentor\Guides\Compiler\CompilerContext;
use phpDocumentor\Guides\Handlers\CompileDocumentsCommand;
use phpDocumentor\Guides\Handlers\ParseDirectoryCommand;
use phpDocumentor\Guides\Handlers\ParseFileCommand;
use phpDocumentor\Guides\Handlers\RenderDocumentCommand;
use phpDocumentor\Guides\Nodes\ProjectNode;
use phpDocumentor\Guides\RenderContext;
use phpDocumentor\Guides\Twig\Theme\ThemeManager;
use phpDocumentor\Guides\UrlGeneratorInterface;

class BuildDocsCommand extends Command
{
    private BuildEnvironment $buildEnvironment;
    private ProjectNode $projectNode;
    private string $theme = 'rtd';

    public function __construct(
        private CommandBus $commandBus,
        private BuildConfig $buildConfig,
        private ThemeManager $themeManager,
        private UrlGeneratorInterface $urlGenerator,
        private LoggerInterface $logger,
    ) {
        parent::__construct('build:docs');
    }

    protected function configure(): void
    {
        $this
            ->addOption('symfony-version', null, InputOption::VALUE_REQUIRED, 'The version of Symfony')
            ->addOption('no-theme', null, InputOption::VALUE_NONE, 'Use the symfony theme instead of the styled one')
            ->addOption('clear-cache', null, InputOption::VALUE_NONE)
            ->addArgument('source-dir', InputArgument::OPTIONAL, 'RST files Source directory', getcwd())
            ->addArgument('output-dir', InputArgument::OPTIONAL, 'HTML files output directory')
        ;
    }

    protected function initialize(InputInterface $input, OutputInterface $output): void
    {
        $this->buildEnvironment = new LocalBuildEnvironment();
        $this->buildEnvironment->setSourceDir($input->getArgument('source-dir'));
        $this->buildEnvironment->setOutputDir($input->getArgument('output-dir'));

        if ($sfVersion = $input->getOption('symfony-version')) {
            $this->buildConfig->setSymfonyVersion($sfVersion);
        }

        if ($input->getOption('no-theme')) {
            $this->theme = 'symfonycom';
        }
        $this->themeManager->useTheme($this->theme);

        $this->projectNode = $this->buildConfig->createProjectNode();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        if ($input->getOption('clear-cache') || !is_file(sys_get_temp_dir().'/guides.cache')) {
            $documents = $this->parse($this->buildEnvironment->getSourceFilesystem());
            file_put_contents(sys_get_temp_dir().'/guides.cache', serialize($documents));
        } else {
            $documents = unserialize(file_get_contents(sys_get_temp_dir().'/guides.cache'));
        }

        $documents = $this->compile($documents);
        $success = $this->render($documents);

        $this->renderThemeAssets();

        return $success ? self::SUCCESS : self::FAILURE;
    }

    private function parse(Filesystem $sourceFilesystem): array
    {
        return $this->commandBus->handle(new ParseDirectoryCommand($sourceFilesystem, '/', 'rst', $this->projectNode));
    }

    private function compile(array $documents): array
    {
        return $this->commandBus->handle(new CompileDocumentsCommand($documents, new CompilerContext($this->projectNode)));
    }

    private function render(array $documents): bool
    {
        $success = true;
        foreach ($documents as $document) {
            try {
                $this->commandBus->handle(new RenderDocumentCommand(
                    $document,
                    RenderContext::forDocument(
                        $document,
                        $this->buildEnvironment->getSourceFilesystem(),
                        $this->buildEnvironment->getOutputFilesystem(),
                        '/',
                        $this->urlGenerator,
                        'html',
                        $this->projectNode
                    )
                ));
            } catch (\Throwable $e) {
                $success = false;
                $this->logger->error($e->getMessage());
            }
        }

        return $success;
    }

    private function renderThemeAssets(): void
    {
        $assetsFilesystem = new Filesystem(new Local(__DIR__.'/../../templates/'.$this->theme));
        $assetsFilesystem->addPlugin(new Finder());

        $outputFilesystem = $this->buildEnvironment->getOutputFilesystem();

        if ($outputFilesystem->has('assets')) {
            $outputFilesystem->deleteDir('assets');
        }

        /** @psalm-suppress UndefinedMagicMethod */
        foreach ($assetsFilesystem->find(new InPath(new Path('assets'))) as $file) {
            /** @psalm-suppress PossiblyFalseArgument */
            $outputFilesystem->write(
                $file['path'],
                $assetsFilesystem->read($file['path'])
            );
        }
    }
}
