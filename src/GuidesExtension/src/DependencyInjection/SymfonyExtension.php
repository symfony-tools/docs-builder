<?php

/*
 * This file is part of the Guides SymfonyExtension package.
 *
 * (c) Wouter de Jong
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SymfonyTools\DocsBuilder\GuidesExtension\DependencyInjection;

use Monolog\Logger;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;
use Symfony\Component\DependencyInjection\Loader\PhpFileLoader;
use Symfony\Component\DependencyInjection\Reference;
use SymfonyTools\DocsBuilder\GuidesExtension\Logger\TraceHandler;

final class SymfonyExtension extends Extension implements PrependExtensionInterface, CompilerPassInterface
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

    public function prepend(ContainerBuilder $container): void
    {
        $templatesDir = \dirname(__DIR__, 2).'/resources/templates';

        $container->prependExtensionConfig('guides', [
            'default_code_language' => 'php',
            'themes' => [
                'symfonycom' => $templatesDir.'/symfonycom/html',
            ],
        ]);

        $container->prependExtensionConfig('code', [
            'languages' => [
                'php' => \dirname(__DIR__, 2).'/resources/highlight.php/php.json',
                'twig' => \dirname(__DIR__, 2).'/resources/highlight.php/twig.json',
                'yaml' => \dirname(__DIR__, 2).'/resources/highlight.php/yaml.json',
            ],
            'aliases' => [
                'caddy' => 'plaintext',
                'env' => 'bash',
                'html+jinja' => 'twig',
                'html+twig' => 'twig',
                'jinja' => 'twig',
                'html+php' => 'html',
                'xml+php' => 'xml',
                'php-annotations' => 'php',
                'php-attributes' => 'php',
                'terminal' => 'bash',
                'rst' => 'markdown',
                'php-standalone' => 'php',
                'php-symfony' => 'php',
                'varnish4' => 'c',
                'varnish3' => 'c',
                'vcl' => 'c',
            ],
        ]);
    }

    public function process(ContainerBuilder $container): void
    {
        if (!$container->hasDefinition(Logger::class)) {
            return;
        }

        $logger = $container->getDefinition(Logger::class);
        $logger->addMethodCall('pushHandler', [new Reference(TraceHandler::class)]);
    }
}
