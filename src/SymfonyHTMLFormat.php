<?php

declare(strict_types=1);

/*
 * This file is part of the Docs Builder package.
 * (c) Ryan Weaver <ryan@symfonycasts.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SymfonyDocsBuilder;

use Doctrine\RST\Directives\DirectiveFactory;
use Doctrine\RST\Formats\Format;
use Doctrine\RST\Nodes\CodeNode;
use Doctrine\RST\Nodes\SpanNode;
use Doctrine\RST\Renderers\CallableNodeRendererFactory;
use Doctrine\RST\Renderers\NodeRendererFactory;
use Doctrine\RST\Templates\TemplateRenderer;
use SymfonyDocsBuilder\CI\UrlChecker;
use Doctrine\RST\HTML\Renderers\SpanNodeRenderer as BaseSpanNodeRenderer;

/**
 * Class SymfonyHTMLFormat.
 */
final class SymfonyHTMLFormat implements Format
{
    private $buildConfig;
    private $templateRenderer;
    private $htmlFormat;
    /** @var UrlChecker|null */
    private $urlChecker;

    public function __construct(BuildConfig $buildConfig, TemplateRenderer $templateRenderer, Format $HTMLFormat, ?UrlChecker $urlChecker = null)
    {
        $this->buildConfig = $buildConfig;
        $this->templateRenderer = $templateRenderer;
        $this->htmlFormat = $HTMLFormat;
        $this->urlChecker = $urlChecker;
    }

    public function getFileExtension(): string
    {
        return Format::HTML;
    }

    /**
     * @return NodeRendererFactory[]
     */
    public function getNodeRendererFactories(): array
    {
        $nodeRendererFactories = $this->htmlFormat->getNodeRendererFactories();

        $nodeRendererFactories[CodeNode::class] = new CallableNodeRendererFactory(
            function (CodeNode $node) {
                return new Renderers\CodeNodeRenderer(
                    $node,
                    $this->templateRenderer
                );
            }
        );

        return $nodeRendererFactories;
    }

    public function getDirectiveFactory(): DirectiveFactory
    {
        return new SymfonyHTMLDirectiveFactory($this->buildConfig, $this->urlChecker);
    }
}
