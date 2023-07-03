<?php

namespace SymfonyTools\GuidesExtension\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;
use Symfony\Component\DependencyInjection\Loader\PhpFileLoader;

class SymfonyExtension extends Extension implements PrependExtensionInterface
{
    public function getAlias(): string
    {
        return 'symfony';
    }

    public function load(array $configs, ContainerBuilder $container): void
    {
        $loader = new PhpFileLoader($container, new FileLocator(\dirname(__DIR__, 2).'/config'));
        $loader->load('services.php');
        $loader->load('parser.php');
        $loader->load('renderer.php');
    }

    public function prepend(ContainerBuilder $container)
    {
        $templatesDir = dirname(__DIR__, 2).'/templates';

        $container->prependExtensionConfig('guides', [
            'themes' => [
                'symfonycom' => $templatesDir.'/symfonycom/html',
                'rtd' => $templatesDir.'/rtd/html',
            ]
        ]);
    }
}
