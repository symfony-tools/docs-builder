<?php

declare(strict_types=1);

/*
 * This file is part of the Docs Builder package.
 * (c) Ryan Weaver <ryan@symfonycasts.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SymfonyDocsBuilder\Listener;

use Doctrine\RST\ErrorManager;
use Doctrine\RST\Event\PreNodeRenderEvent;
use Doctrine\RST\Nodes\TocNode;
use SymfonyDocsBuilder\BuildConfig;
use SymfonyDocsBuilder\Generator\TocGenerator;
use SymfonyDocsBuilder\Toc\TocHandler;

class TocCustomizerListener
{
    private $buildConfig;
    private $errorManager;

    public function __construct(BuildConfig $buildConfig, ErrorManager $errorManager)
    {
        $this->buildConfig = $buildConfig;
        $this->errorManager = $errorManager;
    }

    public function preNodeRender(PreNodeRenderEvent $event)
    {
        $node = $event->getNode();
        if (!$node instanceof TocNode) {
            return;
        }

        $tocMaxDepth = $node->getDepth();
        $metaEntry = '???'; // <--- How can I get this?
        $tocNumItems = (new TocGenerator($metaEntry))->getNumItemsPerLevel();

        $numVisibleTocItems = 0;
        foreach ($tocNumItems as $level => $numItemsInThisLevel) {
            if ($level > $tocMaxDepth) {
                break;
            }

            $numVisibleTocItems += $numItemsInThisLevel;
        }

        $tocSize = $numVisibleTocItems < 10 ? 'md' : ($numVisibleTocItems < 20 ? 'lg' : 'xl');
        $node->setClasses(array_merge($node->getClasses(), ['toc-size-'.$tocSize]));
    }
}
