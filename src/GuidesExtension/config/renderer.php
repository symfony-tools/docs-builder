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

use SymfonyTools\DocsBuilder\GuidesExtension\Highlighter\SymfonyHighlighter;
use SymfonyTools\DocsBuilder\GuidesExtension\NodeRenderer\CodeNodeRenderer;
use SymfonyTools\DocsBuilder\GuidesExtension\NodeRenderer\MenuEntryRenderer;
use SymfonyTools\DocsBuilder\GuidesExtension\Node\ExternalLinkNode;
use SymfonyTools\DocsBuilder\GuidesExtension\Twig\CodeExtension;
use SymfonyTools\DocsBuilder\GuidesExtension\Twig\UrlExtension;
use SymfonyTools\DocsBuilder\GuidesExtension\Renderer\JsonRenderer;
use Twig\Extension\ExtensionInterface;
use Twig\Extra\String\StringExtension;
use phpDocumentor\Guides\Code\Highlighter\Highlighter;
use phpDocumentor\Guides\NodeRenderers\NodeRenderer;
use phpDocumentor\Guides\NodeRenderers\TemplateNodeRenderer;

return static function (ContainerConfigurator $container) {
    $container ->services()
        ->defaults()->autowire()->autoconfigure()
        ->instanceof(ExtensionInterface::class)->tag('twig.extension')
        ->instanceof(NodeRenderer::class)->tag('phpdoc.guides.noderenderer.html', ['priority' => 10])

        ->set(CodeExtension::class)
        ->set(UrlExtension::class)
        ->set(StringExtension::class)

        ->set(CodeNodeRenderer::class)
        ->set(MenuEntryRenderer::class)

        ->set('symfony.node_renderer.html.inline.external_link', TemplateNodeRenderer::class)
            ->arg('$template', 'inline/external-link.html.twig')
            ->arg('$nodeClass', ExternalLinkNode::class)

        ->set(SymfonyHighlighter::class)
            ->decorate(Highlighter::class)

        ->set(JsonRenderer::class)
            ->arg('$nodeRendererFactory', service('phpdoc.guides.noderenderer.factory.json'))
            ->tag('phpdoc.renderer.typerenderer', ['format' => 'json', 'noderender_tag' => 'phpdoc.guides.noderenderer.html'])
    ;
};
