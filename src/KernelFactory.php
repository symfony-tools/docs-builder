<?php declare(strict_types=1);

namespace SymfonyDocsBuilder;

use Doctrine\RST\Configuration as RSTParserConfiguration;
use Doctrine\RST\Event\PostBuildRenderEvent;
use Doctrine\RST\Kernel;
use SymfonyDocsBuilder\Directive as SymfonyDirectives;
use SymfonyDocsBuilder\Listener\CopyImagesDirectoryListener;
use SymfonyDocsBuilder\Reference as SymfonyReferences;

/**
 * Class KernelFactory
 */
final class KernelFactory
{
    public static function createKernel(ConfigBag $configBag): Kernel
    {
        $configuration = new RSTParserConfiguration();
        $configuration->setCustomTemplateDirs([sprintf('%s/src/Templates', $configBag->getBasePath())]);
        $configuration->setCacheDir(sprintf('%s/var/cache', $configBag->getBasePath()));
        $configuration->addFormat(
            new SymfonyHTMLFormat(
                $configuration->getTemplateRenderer(),
                $configuration->getFormat()
            )
        );

        if ($parseOnlyPath = $configBag->getParseOnly()) {
            $configuration->setBaseUrl($configBag->getSymfonyDocUrl());
            $configuration->setBaseUrlEnabledCallable(
                static function (string $path) use ($parseOnlyPath) : bool {
                    return strpos($path, $parseOnlyPath) !== 0;
                }
            );
        }

        $configuration->getEventManager()->addEventListener(
            PostBuildRenderEvent::POST_BUILD_RENDER,
            new CopyImagesDirectoryListener($configBag)
        );

        return new Kernel(
            $configuration,
            self::getDirectives(),
            self::getReferences($configBag)
        );
    }

    private static function getDirectives(): array
    {
        return [
            new SymfonyDirectives\AdmonitionDirective(),
            new SymfonyDirectives\CautionDirective(),
            new SymfonyDirectives\CodeBlockDirective(),
            new SymfonyDirectives\ConfigurationBlockDirective(),
            new SymfonyDirectives\IndexDirective(),
            new SymfonyDirectives\RoleDirective(),
            new SymfonyDirectives\NoteDirective(),
            new SymfonyDirectives\SeeAlsoDirective(),
            new SymfonyDirectives\SidebarDirective(),
            new SymfonyDirectives\TipDirective(),
            new SymfonyDirectives\TopicDirective(),
            new SymfonyDirectives\WarningDirective(),
            new SymfonyDirectives\VersionAddedDirective(),
            new SymfonyDirectives\BestPracticeDirective(),
        ];
    }

    private static function getReferences(ConfigBag $configBag): array
    {
        return [
            new SymfonyReferences\ClassReference($configBag->getSymfonyApiUrl()),
            new SymfonyReferences\MethodReference($configBag->getSymfonyApiUrl()),
            new SymfonyReferences\NamespaceReference($configBag->getSymfonyApiUrl()),
            new SymfonyReferences\PhpFunctionReference($configBag->getPhpDocUrl()),
            new SymfonyReferences\PhpMethodReference($configBag->getPhpDocUrl()),
            new SymfonyReferences\PhpClassReference($configBag->getPhpDocUrl()),
        ];
    }
}
