<?php

/*
 * This file is part of the Docs Builder package.
 * (c) Ryan Weaver <ryan@symfonycasts.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SymfonyDocsBuilder;

use Doctrine\Common\EventManager;
use Doctrine\RST\Builder;
use Doctrine\RST\Configuration;
use Doctrine\RST\ErrorManager;
use Doctrine\RST\Event\PostBuildRenderEvent;
use Doctrine\RST\Event\PreNodeRenderEvent;
use Doctrine\RST\Event\PreParseDocumentEvent;
use Doctrine\RST\Kernel;
use SymfonyDocsBuilder\Listener\AdmonitionListener;
use SymfonyDocsBuilder\Listener\AssetsCopyListener;
use SymfonyDocsBuilder\Listener\CopyImagesListener;
use SymfonyDocsBuilder\Listener\DuplicatedHeaderIdListener;

class DocsKernel extends Kernel
{
    private $buildConfig;

    public function __construct(BuildConfig $buildConfig, ?Configuration $configuration = null, $directives = [], $references = [])
    {
        parent::__construct($configuration, $directives, $references);

        $this->buildConfig = $buildConfig;
    }

    public function initBuilder(Builder $builder): void
    {
        $this->initializeListeners(
            $builder->getConfiguration()->getEventManager(),
            $builder->getErrorManager()
        );

        $builder->setScannerFinder($this->buildConfig->createFileFinder());
    }

    private function initializeListeners(EventManager $eventManager, ErrorManager $errorManager)
    {
        $eventManager->addEventListener(
           PreParseDocumentEvent::PRE_PARSE_DOCUMENT,
           new AdmonitionListener()
       );

        $eventManager->addEventListener(
            PreParseDocumentEvent::PRE_PARSE_DOCUMENT,
            new DuplicatedHeaderIdListener()
        );

        $eventManager->addEventListener(
           PreNodeRenderEvent::PRE_NODE_RENDER,
           new CopyImagesListener($this->buildConfig, $errorManager)
       );

        if (!$this->buildConfig->getSubdirectoryToBuild()) {
            $eventManager->addEventListener(
               [PostBuildRenderEvent::POST_BUILD_RENDER],
               new AssetsCopyListener($this->buildConfig->getOutputDir())
           );
        }
    }
}
