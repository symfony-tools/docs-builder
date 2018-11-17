<?php declare(strict_types=1);

namespace SymfonyDocs;

use Doctrine\RST\Configuration;
use Doctrine\RST\Kernel;
use SymfonyDocs\Directive as SymfonyDoirectives;
use SymfonyDocs\Reference as SymfonyRefernces;

/**
 * Class KernelFactory
 */
final class KernelFactory
{
    public static function createKernel(): Kernel
    {
        $configuration = new Configuration();
        $configuration->setCustomTemplateDirs([__DIR__.'/Templates']);
        $configuration->setCacheDir(__DIR__.'/../var/cache');
        $configuration->addFormat(new SymfonyFormat($configuration->getTemplateRenderer()));

        return new Kernel(
            $configuration,
            self::getDirectives(),
            self::getReferences()
        );
    }

    private static function getDirectives(): array
    {
        return [
            new SymfonyDoirectives\CautionDirective(),
            new SymfonyDoirectives\ClassDirective(),
            new SymfonyDoirectives\CodeBlockDirective(),
            new SymfonyDoirectives\ConfigurationBlockDirective(),
            new SymfonyDoirectives\IndexDirective(),
            new SymfonyDoirectives\NoteDirective(),
            new SymfonyDoirectives\SeeAlsoDirective(),
            new SymfonyDoirectives\SidebarDirective(),
            new SymfonyDoirectives\TipDirective(),
            new SymfonyDoirectives\VersionAddedDirective(),
            new SymfonyDoirectives\BestPracticeDirective(),
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
