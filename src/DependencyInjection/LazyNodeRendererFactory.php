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

use phpDocumentor\Guides\NodeRenderers\InMemoryNodeRendererFactory;
use phpDocumentor\Guides\NodeRenderers\NodeRenderer;
use phpDocumentor\Guides\NodeRenderers\NodeRendererFactory;
use phpDocumentor\Guides\Nodes\Node;
use Psr\Container\ContainerInterface;
use Symfony\Contracts\Service\ServiceSubscriberInterface;

class LazyNodeRendererFactory implements NodeRendererFactory, ServiceSubscriberInterface
{
    public function __construct(
        private ContainerInterface $container
    ) {
    }

    public function get(Node $node): NodeRenderer
    {
        return $this->container->get(NodeRendererFactory::class)->get($node);
    }

    public static function getSubscribedServices(): array
    {
        return [
            NodeRendererFactory::class => InMemoryNodeRendererFactory::class,
        ];
    }
}
