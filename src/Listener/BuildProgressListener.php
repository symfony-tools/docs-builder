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

        // tries to handle progress bar for "rendering"
        $eventManager->addEventListener(
            [PreBuildRenderEvent::PRE_BUILD_RENDER],
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
        if (!\in_array($file, $this->parsedFiles)) {
            $this->parsedFiles[] = $file;
            $this->progressBar->advance();
        }
    }

    public function preBuildRender()
    {
        // finishes the "parse" progress bar
        $this->progressBar->finish();

        $this->io->newLine(2);
        $this->io->note('Rendering the HTML files...');
        // TODO: create a proper progress bar for rendering
    }
}
