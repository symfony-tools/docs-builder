<?php declare(strict_types=1);

namespace SymfonyDocs;

use Doctrine\RST\Configuration;
use Doctrine\RST\Kernel;
use SymfonyDocs\Directive as SymfonyDirectives;
use SymfonyDocs\Reference as SymfonyRefernces;

/**
 * Class KernelFactory
 */
final class KernelFactory
{
    public static function createKernel(string $parseOnlyPath = null): Kernel
    {
        $configuration = new Configuration();
        $configuration->setCustomTemplateDirs([__DIR__.'/Templates']);
        $configuration->setCacheDir(__DIR__.'/../var/cache');
        $configuration->addFormat(
            new SymfonyHTMLFormat(
                $configuration->getTemplateRenderer(),
                $configuration->getFormat()
            )
        );

        if ($parseOnlyPath) {
            $configuration->setBaseUrl(
                sprintf(
                    SymfonyDocConfiguration::getSymfonyDocUrl(),
                    SymfonyDocConfiguration::getVersion()
                )
            );
            $configuration->setBaseUrlEnabledCallable(
                static function (string $path) use ($parseOnlyPath) : bool {
                    return strpos($path, $parseOnlyPath) !== 0;
                }
            );
        }

        return new Kernel(
            $configuration,
            self::getDirectives(),
            self::getReferences()
        );
    }

    private static function getDirectives(): array
    {
        return [
            new SymfonyDirectives\CautionDirective(),
            new SymfonyDirectives\ClassDirective(),
            new SymfonyDirectives\CodeBlockDirective(),
            new SymfonyDirectives\ConfigurationBlockDirective(),
            new SymfonyDirectives\IndexDirective(),
            new SymfonyDirectives\RoleDirective(),
            new SymfonyDirectives\NoteDirective(),
            new SymfonyDirectives\SeeAlsoDirective(),
            new SymfonyDirectives\SidebarDirective(),
            new SymfonyDirectives\TipDirective(),
            new SymfonyDirectives\VersionAddedDirective(),
            new SymfonyDirectives\BestPracticeDirective(),
        ];
    }

    private static function getReferences(): array
    {
        return [
            new SymfonyRefernces\DocReference(),
            new SymfonyRefernces\RefReference(),
            new SymfonyRefernces\ClassReference(),
            new SymfonyRefernces\MethodReference(),
            new SymfonyRefernces\NamespaceReference(),
            new SymfonyRefernces\PhpFunctionReference(),
            new SymfonyRefernces\PhpMethodReference(),
            new SymfonyRefernces\PhpClassReference(),
        ];
    }
}
