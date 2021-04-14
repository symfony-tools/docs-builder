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
use SymfonyDocsBuilder\BuildConfig;

class CopyImagesListener
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

        $newAbsoluteFilePath = $this->buildConfig->getImagesDir().'/'.$fileInfo->getFilename();
        $fs->copy($sourceImage, $newAbsoluteFilePath, true);

        if ('' === $this->buildConfig->getImagesPublicPrefix()) {
            $newUrlPath = $node->getEnvironment()->relativeUrl('_images/'.$fileInfo->getFilename());
        } else {
            $newUrlPath = $this->buildConfig->getImagesPublicPrefix().'/'.$fileInfo->getFilename();
        }
        $node->setValue($newUrlPath);
    }
}
