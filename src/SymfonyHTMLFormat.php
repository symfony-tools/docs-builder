<?php declare(strict_types=1);

namespace SymfonyDocs;

use Doctrine\RST\Formats\Format;
use Doctrine\RST\HTML\HTMLFormat;
use Doctrine\RST\Nodes\CodeNode;
use Doctrine\RST\Nodes\SpanNode;
use Doctrine\RST\Renderers\CallableNodeRendererFactory;
use Doctrine\RST\Renderers\NodeRendererFactory;
use Doctrine\RST\Templates\TemplateRenderer;

/**
 * Class SymfonyHTMLFormat
 */
final class SymfonyHTMLFormat implements Format
{
    /** @var TemplateRenderer */
    protected $templateRenderer;
    /** @var HTMLFormat */
    private $htmlFormat;

    public function __construct(TemplateRenderer $templateRenderer, Format $HTMLFormat)
    {
        $this->templateRenderer = $templateRenderer;
        $this->htmlFormat       = $HTMLFormat;
    }

    public function getFileExtension(): string
    {
        return Format::HTML;
    }

    public function getDirectives(): array
    {
        return $this->htmlFormat->getDirectives();
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

        $nodeRendererFactories[SpanNode::class] = new CallableNodeRendererFactory(
            function (SpanNode $node) {
                return new Renderers\SpanNodeRenderer(
                    $node->getEnvironment(),
                    $node,
                    $this->templateRenderer
                );
            }
        );

        return $nodeRendererFactories;
    }
}
