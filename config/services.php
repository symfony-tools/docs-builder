<?php

/*
 * This file is part of the Docs Builder package.
 *
 * (c) Ryan Weaver <ryan@symfonycasts.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\Log\LoggerInterface;
use SymfonyTools\GuidesExtension\Application;
use SymfonyTools\GuidesExtension\Build\BuildConfig;
use SymfonyTools\GuidesExtension\Command\BuildDocsCommand;
use SymfonyTools\GuidesExtension\DocBuilder;
use SymfonyTools\GuidesExtension\References\PhpResolver;
use SymfonyTools\GuidesExtension\References\SymfonyResolver;
use Symfony\Component\Console\Logger\ConsoleLogger;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\EventDispatcher\EventDispatcher;

return static function (ContainerConfigurator $container) {
    $container->services()
        ->defaults()->autowire()

        ->set(OutputInterface::class, ConsoleOutput::class)

        ->set(Application::class)->public()
            ->call('add', [inline_service(BuildDocsCommand::class)->autowire()])
            ->call('setDispatcher', [service(EventDispatcher::class)])

        ->set(LoggerInterface::class, ConsoleLogger::class)

        ->set(EventDispatcher::class)
        ->alias(EventDispatcherInterface::class, EventDispatcher::class)

        ->set(BuildConfig::class)

        ->set(DocBuilder::class)->public()
    ;
};
