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
use Psr\Log\LogLevel;
use Symfony\Component\EventDispatcher\EventDispatcher;
use SymfonyTools\DocsBuilder\GuidesExtension\Build\BuildConfig;
use SymfonyTools\DocsBuilder\GuidesExtension\DocBuilder;
use SymfonyTools\DocsBuilder\GuidesExtension\Logger\TraceHandler;

return static function (ContainerConfigurator $container) {
    $container->services()
        ->defaults()->autowire()

        ->set(EventDispatcherInterface::class, EventDispatcher::class)

        ->set(TraceHandler::class)
            ->arg('$level', LogLevel::WARNING)

        ->set(BuildConfig::class)->public()

        ->set(DocBuilder::class)->public()
    ;
};
