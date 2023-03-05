<?php

declare(strict_types=1);

/*
 * This file is part of the Docs Builder package.
 * (c) Ryan Weaver <ryan@symfonycasts.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SymfonyDocsBuilder;

use Doctrine\Common\EventManager;
use Doctrine\RST\Builder;
use Doctrine\RST\Configuration as RSTParserConfiguration;
use Doctrine\RST\Event\PostBuildRenderEvent;
use Doctrine\RST\Event\PreNodeRenderEvent;
use Doctrine\RST\Event\PreParseDocumentEvent;
use SymfonyDocsBuilder\Listener\AssetsCopyListener;
use Doctrine\RST\ErrorManager;
use SymfonyDocsBuilder\Listener\CopyImagesListener;
use SymfonyDocsBuilder\Listener\AdmonitionListener;
use SymfonyDocsBuilder\CI\UrlChecker;
use SymfonyDocsBuilder\Twig\AssetsExtension;
use SymfonyDocsBuilder\Twig\TocExtension;
use function Symfony\Component\String\u;

final class BuilderFactory
{
    public static function createBuilder(BuildConfig $buildConfig, ?UrlChecker $urlChecker = null): Builder
    {
        $configuration = new RSTParserConfiguration();
        // needed to avoid outputting parser errors on the console output or the webpage contents
        $configuration->silentOnError(true);
        $configuration->setCustomTemplateDirs([__DIR__.'/Templates']);
        $configuration->setTheme($buildConfig->getTheme());
        $configuration->setCacheDir(sprintf('%s/var/cache', $buildConfig->getCacheDir()));
        $configuration->abortOnError(false);

        if (!$buildConfig->isBuildCacheEnabled()) {
            $configuration->setUseCachedMetas(false);
        }

        $configuration->addFormat(
            new SymfonyHTMLFormat(
                $buildConfig,
                $configuration->getTemplateRenderer(),
                $configuration->getFormat(),
                $urlChecker
            )
        );

        if ($parseSubPath = $buildConfig->getSubdirectoryToBuild()) {
            $configuration->setBaseUrl($buildConfig->getSymfonyDocUrl());
            $configuration->setBaseUrlEnabledCallable(
                static function (string $path) use ($parseSubPath): bool {
                    return u($path)->containsAny($parseSubPath);
                }
            );
        }

        $eventManager = $configuration->getEventManager();
        $eventManager->addEventListener(
           PreParseDocumentEvent::PRE_PARSE_DOCUMENT,
           new AdmonitionListener()
       );

        $eventManager->addEventListener(
           PreNodeRenderEvent::PRE_NODE_RENDER,
           new CopyImagesListener($buildConfig, $configuration->getErrorManager())
       );

        if (!$buildConfig->getSubdirectoryToBuild()) {
            $eventManager->addEventListener(
               [PostBuildRenderEvent::POST_BUILD_RENDER],
               new AssetsCopyListener($buildConfig->getOutputDir())
           );
        }

        $twig = $configuration->getTemplateEngine();
        $twig->addExtension(new AssetsExtension());
        $twig->addExtension(new TocExtension());

        $builder = new Builder($configuration);
        $builder->setScannerFinder($buildConfig->createFileFinder());

        return $builder;
    }
}
