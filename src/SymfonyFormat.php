<?php declare(strict_types=1);

namespace SymfonyDocs;

use Doctrine\RST\HTML\HTMLFormat;
use Doctrine\RST\Nodes\CodeNode;
use Doctrine\RST\Nodes\SpanNode;
use Doctrine\RST\Renderers\CallableNodeRendererFactory;
use Doctrine\RST\Renderers\NodeRendererFactory;
use Doctrine\RST\Templates\TemplateRenderer;

/**
 * Class SymfonyFormat
 */
final class SymfonyFormat extends HTMLFormat
{
    /** @var TemplateRenderer */
    protected $templateRenderer;

    public function __construct(TemplateRenderer $templateRenderer)
    {
        parent::__construct($templateRenderer);
        $this->templateRenderer = $templateRenderer;
    }

    /**
     * @return NodeRendererFactory[]
     */
    public function getNodeRendererFactories(): array
    {
        $nodeRendererFactories = parent::getNodeRendererFactories();

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
