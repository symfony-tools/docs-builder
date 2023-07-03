<?php

/*
 * This file is part of the Docs Builder package.
 *
 * (c) Ryan Weaver <ryan@symfonycasts.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SymfonyTools\GuidesExtension\DependencyInjection;

use Psr\Container\ContainerInterface;
use Symfony\Contracts\Service\ServiceSubscriberInterface;
use phpDocumentor\Guides\NodeRenderers\NodeRenderer;
use phpDocumentor\Guides\NodeRenderers\NodeRendererFactory;
use phpDocumentor\Guides\NodeRenderers\InMemoryNodeRendererFactory;
use phpDocumentor\Guides\Nodes\Node;

class LazyNodeRendererFactory implements NodeRendererFactory, ServiceSubscriberInterface
{
    public function __construct(
        private ContainerInterface $container
    ) {}

    public function get(Node $node): NodeRenderer
    {
        return $this->container->get(NodeRendererFactory::class)->get($node);
    }

    public static function getSubscribedServices(): array
    {
        return [
            NodeRendererFactory::class => InMemoryNodeRendererFactory::class
        ];
    }
}
