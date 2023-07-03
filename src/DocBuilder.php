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
use phpDocumentor\Guides\UrlGeneratorInterface;

final class DocBuilder
{
    public function __construct(
        private CommandBus $commandBus,
        private ThemeManager $themeManager,
        private BuildConfig $buildConfig,
        private UrlGeneratorInterface $urlGenerator,
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
                    $buildEnvironment->getSourceFilesystem(),
                    $buildEnvironment->getOutputFilesystem(),
                    '/',
                    $this->urlGenerator,
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

        return $buildEnvironment->getOutputFilesystem()->read('/index.html');
    }
}
