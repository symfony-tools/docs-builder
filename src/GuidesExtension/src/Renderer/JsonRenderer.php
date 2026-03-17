<?php

/*
 * This file is part of the Guides SymfonyExtension package.
 *
 * (c) Wouter de Jong
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SymfonyTools\DocsBuilder\GuidesExtension\Renderer;

use phpDocumentor\Guides\Handlers\RenderCommand;
use phpDocumentor\Guides\NodeRenderers\NodeRendererFactory;
use phpDocumentor\Guides\Nodes\DocumentNode;
use phpDocumentor\Guides\Nodes\Node;
use phpDocumentor\Guides\RenderContext;
use phpDocumentor\Guides\Renderer\TypeRenderer;
use phpDocumentor\Guides\Renderer\UrlGenerator\UrlGeneratorInterface;

final class JsonRenderer implements TypeRenderer
{
    public function __construct(
        private NodeRendererFactory $nodeRendererFactory,
        private UrlGeneratorInterface $urlGenerator,
    ) {
    }

    public function render(RenderCommand $renderCommand): void
    {
        $projectRenderContext = RenderContext::forProject(
            $renderCommand->getProjectNode(),
            $renderCommand->getDocumentArray(),
            $renderCommand->getOrigin(),
            $renderCommand->getDestination(),
            $renderCommand->getDestinationPath(),
            $renderCommand->getOutputFormat(),
        )->withIterator($renderCommand->getDocumentIterator());

        foreach ($projectRenderContext->getIterator() as $documentNode) {
            $context = $projectRenderContext->withDocument($documentNode);
            $html = implode(
                "\n",
                array_map(fn (Node $node): string => $this->nodeRendererFactory->get($node)->render($node, $context), $documentNode->getChildren())
            );

            $prevDocument = $nextDocument = null;
            if (!$documentNode->isOrphan()) {
                $prevDocument = $context->getIterator()->previousNode();
                $nextDocument = $context->getIterator()->nextNode();
            }
            $context->getDestination()->put(
                $context->getDestinationPath().'/'.$context->getCurrentFileName().'.fjson',
                json_encode([
                    'parents' => [],
                    'prev' => $this->getDocumentData($context, $prevDocument),
                    'next' => $this->getDocumentData($context, $nextDocument),
                    'title' => $documentNode->getTitle()?->toString() ?? '',
                    'body' => $html,
                ], \JSON_PRETTY_PRINT)
            );
        }
    }

    private function getDocumentData($context, ?DocumentNode $document): ?array
    {
        if (null === $document || $document->isOrphan()) {
            return null;
        }

        $url = $this->urlGenerator->createFileUrl($context, $document->getFilePath());

        return [
            'title' => $document->getTitle()?->toString() ?? '',
            'link' => substr($url, 0, strrpos($url, '.')).'.html',
        ];
    }
}
