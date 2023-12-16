<?php

namespace SymfonyTools\GuidesExtension\NodeRenderer;

use phpDocumentor\Guides\Code\Highlighter\Highlighter;
use phpDocumentor\Guides\NodeRenderers\NodeRenderer;
use phpDocumentor\Guides\Nodes\CodeNode;
use phpDocumentor\Guides\Nodes\Node;
use phpDocumentor\Guides\RenderContext;
use phpDocumentor\Guides\TemplateRenderer;

/**
 * @implements NodeRenderer<CodeNode>
 */
class CodeNodeRenderer implements NodeRenderer
{
    public function __construct(
        private TemplateRenderer $renderer,
        private Highlighter $higlighter,
    ) {
    }

    public function supports(Node $node): bool
    {
        return $node instanceof CodeNode;
    }

    public function render(Node $node, RenderContext $renderContext): string
    {
        if (!$node instanceof CodeNode) {
            throw new \LogicException(sprintf('"%s" can only render code nodes, got "%s".', __CLASS__, \get_debug_type($node)));
        }

        $language = $node->getLanguage() ?? 'text';
        $highlight = ($this->higlighter)($language, $node->getValue());

        $languages = array_unique([$language, $highlight->language]);
        $code = $highlight->code;

        $numOfLines = \count(preg_split('/\R/', trim($code)));
        $lineNumbers = implode("\n", range(1, $numOfLines));

        return $this->renderer->renderTemplate(
            $renderContext,
            'body/code.html.twig',
            [
                'languages' => $languages,
                'code' => rtrim($code),
                'line_numbers' => $lineNumbers,
                'loc' => $numOfLines,
                'node' => $node,
            ]
        );
    }
}
