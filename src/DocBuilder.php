<?php

namespace SymfonyTools\GuidesExtension;

use League\Tactician\CommandBus;
use SymfonyTools\GuidesExtension\Build\BuildConfig;
use SymfonyTools\GuidesExtension\Build\BuildEnvironment;
use SymfonyTools\GuidesExtension\Build\MemoryBuildEnvironment;
use phpDocumentor\Guides\Compiler\CompilerContext;
use phpDocumentor\Guides\Handlers\CompileDocumentsCommand;
use phpDocumentor\Guides\Handlers\ParseDirectoryCommand;
use phpDocumentor\Guides\Handlers\RenderDocumentCommand;
use phpDocumentor\Guides\Nodes\DocumentNode;
use phpDocumentor\Guides\RenderContext;
use phpDocumentor\Guides\Twig\Theme\ThemeManager;

final class DocBuilder
{
    public function __construct(
        private CommandBus $commandBus,
        private ThemeManager $themeManager,
        private BuildConfig $buildConfig,
    ) {
    }

    public function build(BuildEnvironment $buildEnvironment): void
    {
        $this->themeManager->useTheme('symfonycom');

        $projectNode = $this->buildConfig->createProjectNode();

        /** @var list<DocumentNode> $documents */
        $documents = $this->commandBus->handle(new ParseDirectoryCommand($buildEnvironment->getSourceFilesystem(), '/', 'rst', $projectNode));

        $documents = $this->commandBus->handle(new CompileDocumentsCommand($documents, new CompilerContext($projectNode)));

        foreach ($documents as $document) {
            $this->commandBus->handle(new RenderDocumentCommand(
                $document,
                RenderContext::forDocument(
                    $document,
                    $documents,
                    $buildEnvironment->getSourceFilesystem(),
                    $buildEnvironment->getOutputFilesystem(),
                    '/',
                    'html',
                    $projectNode
                )
            ));
        }
    }

    public function buildString(string $contents): string
    {
        $buildEnvironment = new MemoryBuildEnvironment();
        $buildEnvironment->getSourceFilesystem()->write('/index.rst', $contents);

        $this->build($buildEnvironment);

        $output = $buildEnvironment->getOutputFilesystem()->read('/index.html');
        if (false === $output) {
            throw new \LogicException('Cannot build HTML from the provided reStructuredText: no HTML output found.');
        }

        return $output;
    }
}
