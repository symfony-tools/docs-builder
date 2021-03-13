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
use Doctrine\RST\Nodes\ImageNode;
use Symfony\Component\Filesystem\Filesystem;
use SymfonyDocsBuilder\BuildContext;

class CopyImagesListener
{
    private $buildContext;
    private $errorManager;

    public function __construct(BuildContext $buildContext, ErrorManager $errorManager)
    {
        $this->buildContext = $buildContext;
        $this->errorManager = $errorManager;
    }

    public function preNodeRender(PreNodeRenderEvent $event)
    {
        $node = $event->getNode();
        if (!$node instanceof ImageNode) {
            return;
        }

        $sourceImage = $node->getEnvironment()->absoluteRelativePath($node->getUrl());

        if (!file_exists($sourceImage)) {
            $this->errorManager->error(sprintf(
                'Missing image file "%s" in "%s"',
                $node->getUrl(),
                $node->getEnvironment()->getCurrentFileName()
            ));

            return;
        }

        $fileInfo = new \SplFileInfo($sourceImage);
        $fs = new Filesystem();

        $newAbsoluteFilePath = $this->buildContext->getPublicImagesDir().'/'.$fileInfo->getFilename();
        $newUrlPath = $this->buildContext->getPublicImagesPrefix().'/'.$fileInfo->getFilename();

        $fs->copy($sourceImage, $newAbsoluteFilePath, true);
        $node->setValue($newUrlPath);
    }
}
