<?php

/*
 * This file is part of the Guides SymfonyExtension package.
 *
 * (c) Wouter de Jong
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SymfonyTools\GuidesExtension;

use Monolog\Logger;
use phpDocumentor\Guides\Cli\DependencyInjection\ContainerFactory;
use phpDocumentor\Guides\Code\DependencyInjection\CodeExtension;
use phpDocumentor\Guides\RestructuredText\DependencyInjection\ReStructuredTextExtension;
use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Alias;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;
use Symfony\Component\EventDispatcher\EventDispatcher;
use SymfonyTools\GuidesExtension\DependencyInjection\SymfonyExtension;

final class DocsKernel
{
    public function __construct(
        private Container $container
    ) {
    }

    /** @param list<ExtensionInterface> $extensions */
    public static function create(array $extensions = []): self
    {
        $containerFactory = new ContainerFactory([new SymfonyExtension(), self::createDefaultExtension(), new CodeExtension(), ...$extensions]);

        for ($i = 1; $i <= 4; ++$i) {
            if (is_dir($vendor = \dirname(__DIR__, $i).'/vendor')) {
                break;
            }
        }

        $containerFactory->loadExtensionConfig(RestructuredTextExtension::class, [
            'default_code_language' => 'php',
        ]);

        $container = $containerFactory->create($vendor);

        return new self($container);
    }

    /**
     * @template T
     *
     * @param class-string<T> $fqcn
     *
     * @return T
     *
     * @psalm-suppress InvalidReturnType
     * @psalm-suppress InvalidReturnStatement
     */
    public function get(string $fqcn): object
    {
        return $this->container->get($fqcn);
    }

    private static function createDefaultExtension(): ExtensionInterface
    {
        return new class() extends Extension {
            public function load(array $configs, ContainerBuilder $container): void
            {
                $container->register(Logger::class)->setArgument('$name', 'docs-builder');
                $container->setAlias(LoggerInterface::class, new Alias(Logger::class));

                $container->register(EventDispatcher::class);
                $container->setAlias(EventDispatcherInterface::class, new Alias(EventDispatcher::class));
            }

            public function getAlias(): string
            {
                return 'docs-builder';
            }
        };
    }
}
