<?php

/*
 * This file is part of the Guides SymfonyExtension package.
 *
 * (c) Wouter de Jong
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SymfonyDocsBuilder;

use League\Tactician\CommandBus;
use phpDocumentor\Guides\Compiler\CompilerContext;
use phpDocumentor\Guides\Handlers\CompileDocumentsCommand;
use phpDocumentor\Guides\Handlers\ParseDirectoryCommand;
use phpDocumentor\Guides\Handlers\RenderDocumentCommand;
use phpDocumentor\Guides\Nodes\DocumentNode;
use phpDocumentor\Guides\RenderContext;
use phpDocumentor\Guides\Twig\Theme\ThemeManager;
use SymfonyDocsBuilder\Build\BuildConfig;
use SymfonyDocsBuilder\Build\BuildEnvironment;
use SymfonyDocsBuilder\Build\StringBuildEnvironment;

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
        $buildEnvironment = new StringBuildEnvironment($contents);

        $this->build($buildEnvironment);

        $output = $buildEnvironment->getOutput();
        if (null === $output) {
            throw new \LogicException('Cannot build HTML from the provided reStructuredText: no HTML output found.');
        }

        return $output;
    }
}
