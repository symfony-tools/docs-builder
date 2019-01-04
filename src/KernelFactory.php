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
    public static function createKernel(BuildContext $buildContext): Kernel
    {
        $configuration = new RSTParserConfiguration();
        $configuration->setCustomTemplateDirs([sprintf('%s/src/Templates', $buildContext->getBasePath())]);
        $configuration->setCacheDir(sprintf('%s/var/cache', $buildContext->getBasePath()));
        $configuration->addFormat(
            new SymfonyHTMLFormat(
                $configuration->getTemplateRenderer(),
                $configuration->getFormat()
            )
        );

        if ($parseOnlyPath = $buildContext->getParseOnly()) {
            $configuration->setBaseUrl($buildContext->getSymfonyDocUrl());
            $configuration->setBaseUrlEnabledCallable(
                static function (string $path) use ($parseOnlyPath) : bool {
                    return strpos($path, $parseOnlyPath) !== 0;
                }
            );
        }

        $configuration->getEventManager()->addEventListener(
            PostBuildRenderEvent::POST_BUILD_RENDER,
            new CopyImagesDirectoryListener($buildContext)
        );

        return new Kernel(
            $configuration,
            self::getDirectives(),
            self::getReferences($buildContext)
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

    private static function getReferences(BuildContext $buildContext): array
    {
        return [
            new SymfonyReferences\ClassReference($buildContext->getSymfonyApiUrl()),
            new SymfonyReferences\MethodReference($buildContext->getSymfonyApiUrl()),
            new SymfonyReferences\NamespaceReference($buildContext->getSymfonyApiUrl()),
            new SymfonyReferences\PhpFunctionReference($buildContext->getPhpDocUrl()),
            new SymfonyReferences\PhpMethodReference($buildContext->getPhpDocUrl()),
            new SymfonyReferences\PhpClassReference($buildContext->getPhpDocUrl()),
        ];
    }
}
