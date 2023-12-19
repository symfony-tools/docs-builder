<?php

/*
 * This file is part of the Guides SymfonyExtension package.
 *
 * (c) Wouter de Jong
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SymfonyTools\GuidesExtension\DependencyInjection;

use League\Tactician\Exception\MissingHandlerException;
use League\Tactician\Handler\Locator\HandlerLocator;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;

class CommandLocator implements HandlerLocator
{
    public function __construct(
        private ContainerInterface $commands
    ) {
    }

    public function getHandlerForCommand($commandName): object
    {
        try {
            return $this->commands->get($commandName);
        } catch (NotFoundExceptionInterface) {
            throw new MissingHandlerException(sprintf('No handler found for command "%s"', $commandName));
        }
    }
}
