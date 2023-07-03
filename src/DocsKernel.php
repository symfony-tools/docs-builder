<?php

/*
 * This file is part of the Docs Builder package.
 *
 * (c) Ryan Weaver <ryan@symfonycasts.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SymfonyTools\GuidesExtension;

use SymfonyTools\GuidesExtension\DependencyInjection\SymfonyExtension;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;
use Symfony\Component\DependencyInjection\Loader\PhpFileLoader;
use Symfony\Component\DependencyInjection\Reference;
use phpDocumentor\Guides\DependencyInjection\GuidesExtension;
use phpDocumentor\Guides\NodeRenderers\NodeRendererFactory;
use phpDocumentor\Guides\NodeRenderers\NodeRendererFactoryAware;
use phpDocumentor\Guides\RestructuredText\DependencyInjection\ReStructuredTextExtension;

final class DocsKernel
{
    public function __construct(
        private Container $container
    ) {}

    public static function create(array $extensions = []): self
    {
        $container = new ContainerBuilder();

        for ($i = 1; $i <= 4; $i++) {
            if (is_dir($vendor = dirname(__DIR__, $i).'/vendor')) {
                $container->setParameter('vendor_dir', $vendor);
                break;
            }
        }

        foreach (array_merge($extensions, self::createDefaultExtensions()) as $extension) {
            $container->registerExtension($extension);
            $container->loadFromExtension($extension->getAlias());

            if ($extension instanceof CompilerPassInterface) {
                $container->addCompilerPass($extension);
            }
        }

        $container->compile();

        return new self($container);
    }

    /**
     * @template T
     * @param class-string<T> $fqcn
     * @return T
     *
     * @psalm-suppress InvalidReturnType
     * @psalm-suppress InvalidReturnStatement
     */
    public function get(string $fqcn): object
    {
        return $this->container->get($fqcn);
    }

    /** @return list<ExtensionInterface> */
    private static function createDefaultExtensions(): array
    {
        return [
            new GuidesExtension(),
            new ReStructuredTextExtension(),
            new SymfonyExtension(),
        ];
    }
}
