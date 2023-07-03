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

use Highlight\Highlighter as HighlightPHP;
use SymfonyTools\GuidesExtension\Highlighter\Highlighter;
use SymfonyTools\GuidesExtension\NodeRenderer\CodeNodeRenderer;
use SymfonyTools\GuidesExtension\Node\ConfigurationBlockNode;
use SymfonyTools\GuidesExtension\Node\ExternalLinkNode;
use SymfonyTools\GuidesExtension\Twig\CodeExtension;
use SymfonyTools\GuidesExtension\Twig\UrlExtension;
use Twig\Extension\ExtensionInterface;
use Twig\Extra\String\StringExtension;
use phpDocumentor\Guides\NodeRenderers\NodeRenderer;
use phpDocumentor\Guides\NodeRenderers\TemplateNodeRenderer;

return static function (ContainerConfigurator $container) {
    $container ->services()
        ->defaults()->autowire()->autoconfigure()
        ->instanceof(ExtensionInterface::class)->tag('twig.extension')
        ->instanceof(NodeRenderer::class)->tag('phpdoc.guides.noderenderer.html')

        ->set(CodeExtension::class)
        ->set(UrlExtension::class)
        ->set(StringExtension::class)

        ->set(CodeNodeRenderer::class)

        ->set('symfony.node_renderer.html.configuration_block', TemplateNodeRenderer::class)
            ->arg('$template', 'body/configuration-block.html.twig')
            ->arg('$nodeClass', ConfigurationBlockNode::class)

        ->set('symfony.node_renderer.html.inline.external_link', TemplateNodeRenderer::class)
            ->arg('$template', 'inline/external-link.html.twig')
            ->arg('$nodeClass', ExternalLinkNode::class)

        ->set(Highlighter::class)
            ->args([
                inline_service(HighlightPHP::class)
                    ->call('registerLanguage', ['php', dirname(__DIR__, 1).'/templates/highlight.php/php.json', true])
                    ->call('registerLanguage', ['twig', dirname(__DIR__, 1).'/templates/highlight.php/twig.json', true])
            ])
    ;
};
