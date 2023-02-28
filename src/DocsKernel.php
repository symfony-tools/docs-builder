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
use Doctrine\RST\Parser;
use SymfonyDocsBuilder\Listener\AdmonitionListener;
use SymfonyDocsBuilder\Listener\AssetsCopyListener;
use SymfonyDocsBuilder\Listener\CopyImagesListener;

class DocsKernel extends Kernel
{
    private $buildConfig;

    public function __construct(BuildConfig $buildConfig, ?Configuration $configuration = null, $directives = [], $references = [])
    {
        parent::__construct($configuration, $directives, $references);

        $this->buildConfig = $buildConfig;
    }

    public function createBuilder(): Builder
    {
        return new Builder($this->getConfiguration(), $this);
    }

    public function initBuilder(Builder $builder): void
    {
        $configuration = $builder->getConfiguration();
        $this->initializeListeners(
            $configuration->getEventManager(),
            $configuration->getErrorManager()
        );

        $builder->setScannerFinder($this->buildConfig->createFileFinder());
    }

    public function createParser(): Parser
    {
        return new Parser($this->getConfiguration(), $this);
    }

    private function initializeListeners(EventManager $eventManager, ErrorManager $errorManager)
    {
        $eventManager->addEventListener(
           PreParseDocumentEvent::PRE_PARSE_DOCUMENT,
           new AdmonitionListener()
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
