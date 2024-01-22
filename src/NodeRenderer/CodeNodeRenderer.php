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
use phpDocumentor\Guides\Nodes\CodeNode;
use phpDocumentor\Guides\Nodes\Node;
use phpDocumentor\Guides\RenderContext;
use phpDocumentor\Guides\TemplateRenderer;
use SymfonyTools\GuidesExtension\Highlighter\SymfonyHighlighter;

/**
 * @implements NodeRenderer<CodeNode>
 */
class CodeNodeRenderer implements NodeRenderer
{
    public function __construct(
        private TemplateRenderer $renderer,
        private SymfonyHighlighter $higlighter,
    ) {
    }

    public function supports(string $nodeFqcn): bool
    {
        return $nodeFqcn === CodeNode::class || is_a($nodeFqcn, CodeNode::class, true);
    }

    public function render(Node $node, RenderContext $renderContext): string
    {
        if (!$node instanceof CodeNode) {
            throw new \LogicException(sprintf('"%s" can only render code nodes, got "%s".', __CLASS__, get_debug_type($node)));
        }

        $language = $node->getLanguage() ?? 'text';
        $highlight = ($this->higlighter)($language, $node->getValue(), $renderContext->getLoggerInformation());

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
