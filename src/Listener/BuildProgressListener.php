<?php

declare(strict_types=1);

/*
 * This file is part of the Docs Builder package.
 * (c) Ryan Weaver <ryan@symfonycasts.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SymfonyDocsBuilder\Listener;

use Doctrine\Common\EventManager;
use Doctrine\RST\Event\PostBuildRenderEvent;
use Doctrine\RST\Event\PostNodeRenderEvent;
use Doctrine\RST\Event\PostParseDocumentEvent;
use Doctrine\RST\Event\PreBuildParseEvent;
use Doctrine\RST\Event\PreBuildRenderEvent;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Style\SymfonyStyle;

class BuildProgressListener
{
    private $io;
    private $progressBar;
    private $parsedFiles = [];
    private $renderProgressBar;
    private $renderedFiles = [];

    public function __construct(SymfonyStyle $io)
    {
        $this->io = $io;
        $this->progressBar = new ProgressBar($io);
    }

    public function attachListeners(EventManager $eventManager)
    {
        // sets up the "parsing" progress bar
        $eventManager->addEventListener(
            [PreBuildParseEvent::PRE_BUILD_PARSE],
            $this
        );

        // advances "parsing" progress bar
        $eventManager->addEventListener(
            [PostParseDocumentEvent::POST_PARSE_DOCUMENT],
            $this
        );

        // sets up the "rendering" progress bar
        $eventManager->addEventListener(
            [PreBuildRenderEvent::PRE_BUILD_RENDER],
            $this
        );

        // advances the "rendering" progress bar
        $eventManager->addEventListener(
            [PostNodeRenderEvent::POST_NODE_RENDER],
            $this
        );

        // finishes the "rendering" progress bar
        $eventManager->addEventListener(
            [PostBuildRenderEvent::POST_BUILD_RENDER],
            $this
        );
    }

    /**
     * Called very early: used to initialize the "parsing" progress bar.
     *
     * @param PreBuildParseEvent $event
     */
    public function preBuildParse(PreBuildParseEvent $event)
    {
        $parseQueue = $event->getParseQueue();
        $parseCount = \count($parseQueue->getAllFilesThatRequireParsing());
        $this->io->note(sprintf('Start parsing %d out-of-date rst files', $parseCount));
        $this->progressBar->setMaxSteps($parseCount);
    }

    public function postParseDocument(PostParseDocumentEvent $postParseDocumentEvent): void
    {
        $file = $postParseDocumentEvent->getDocumentNode()->getEnvironment()->getCurrentFileName();
        if (!\in_array($file, $this->parsedFiles, true)) {
            $this->parsedFiles[] = $file;
            $this->progressBar->advance();
        }
    }

    /**
     * Called after parsing: finishes the "parse" progress bar and
     * initializes the "rendering" one.
     */
    public function preBuildRender(PreBuildRenderEvent $event)
    {
        // finishes the "parse" progress bar
        $this->progressBar->finish();

        $this->io->newLine(2);

        $renderCount = \count($event->getBuilder()->getDocuments()->getAll());
        $this->io->note(sprintf('Rendering %d HTML files', $renderCount));

        $this->renderProgressBar = new ProgressBar($this->io);
        $this->renderProgressBar->setMaxSteps($renderCount);
    }

    /**
     * The parser doesn't dispatch any event per rendered document, so the
     * progress is tracked by looking at the file each rendered node belongs to.
     */
    public function postNodeRender(PostNodeRenderEvent $event): void
    {
        // nodes are also rendered before the "rendering" phase starts
        if (null === $this->renderProgressBar) {
            return;
        }

        $environment = $event->getRenderedNode()->getNode()->getEnvironment();

        if (null === $environment) {
            return;
        }

        $file = $environment->getCurrentFileName();

        if (!\in_array($file, $this->renderedFiles, true)) {
            $this->renderedFiles[] = $file;
            $this->renderProgressBar->advance();
        }
    }

    public function postBuildRender()
    {
        $this->renderProgressBar->finish();

        $this->io->newLine(2);
    }
}
