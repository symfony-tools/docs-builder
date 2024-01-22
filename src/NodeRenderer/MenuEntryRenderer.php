<?php

/*
 * This file is part of the Guides SymfonyExtension package.
 *
 * (c) Wouter de Jong
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SymfonyTools\GuidesExtension\NodeRenderer;

use phpDocumentor\Guides\NodeRenderers\NodeRenderer;
use phpDocumentor\Guides\Nodes\Menu\MenuEntryNode;
use phpDocumentor\Guides\Nodes\Menu\SectionMenuEntryNode;
use phpDocumentor\Guides\Nodes\Node;
use phpDocumentor\Guides\RenderContext;
use phpDocumentor\Guides\Renderer\UrlGenerator\UrlGeneratorInterface;
use phpDocumentor\Guides\TemplateRenderer;

/**
 * @implements NodeRenderer<MenuEntryNode>
 */
class MenuEntryRenderer implements NodeRenderer
{
    public function __construct(
        private TemplateRenderer $renderer,
        private UrlGeneratorInterface $urlGenerator,
    ) {
    }

    public function supports(string $nodeFqcn): bool
    {
        return $nodeFqcn === MenuEntryNode::class || is_a($nodeFqcn, MenuEntryNode::class, true);
    }

    public function render(Node $node, RenderContext $renderContext): string
    {
        if (!$node instanceof MenuEntryNode) {
            throw new \LogicException(sprintf('"%s" can only render menu entry nodes, got "%s".', __CLASS__, get_debug_type($node)));
        }

        $url = $this->urlGenerator->generateCanonicalOutputUrl(
            $renderContext,
            $node->getUrl(),
            $node instanceof SectionMenuEntryNode ? $node->getValue()?->getId() : null
        );

        return $this->renderer->renderTemplate(
            $renderContext,
            'body/menu/menu-item.html.twig',
            [
                'url' => $url,
                'node' => $node,
            ],
        );
    }
}
