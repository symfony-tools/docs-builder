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
use phpDocumentor\Guides\Nodes\DocumentTree\DocumentEntryNode;
use phpDocumentor\Guides\Nodes\DocumentTree\SectionEntryNode;
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
            $renderCommand->getImageDestination(),
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

            $documentEntry = $documentNode->getDocumentEntry();
            $toc = array_map(fn (SectionEntryNode $section): array => $this->getJsonToc($context, $documentEntry, $section), $documentEntry->getSections()[0]->getChildren());
            $context->getDestination()->put(
                $context->getDestinationPath().'/'.$context->getCurrentFileName().'.fjson',
                json_encode([
                    'parents' => [],
                    'toc' => $toc,
                    'toc_options' => [
                        'maxDepth' => 2,
                        'numVisibleItems' => array_sum(array_map(static fn ($t) => 1 + \count($t['children']), $toc)),
                    ],
                    'prev' => $this->getDocumentData($context, $prevDocument),
                    'next' => $this->getDocumentData($context, $nextDocument),
                    'title' => $documentNode->getTitle()?->toString() ?? '',
                    'current_page_name' => $context->getCurrentFileName(),
                    'body' => $html,
                ], \JSON_PRETTY_PRINT)
            );
        }
    }

    private function getJsonToc(RenderContext $context, DocumentEntryNode $documentEntry, SectionEntryNode $sectionEntry): ?array
    {
        if ($sectionEntry->getTitle()->getLevel() > 3) {
            return null;
        }

        $url = $this->urlGenerator->createFileUrl($context, $documentEntry->getFile());

        return [
            'level' => $sectionEntry->getTitle()->getLevel() - 1,
            'url' => $url.'#'.$sectionEntry->getId(),
            'page' => $documentEntry->getFile(),
            'fragment' => $sectionEntry->getId(),
            'title' => $sectionEntry->getTitle()->toString(),
            'children' => array_filter(array_map(fn (SectionEntryNode $section): ?array => $this->getJsonToc($context, $documentEntry, $section), $sectionEntry->getChildren())),
        ];
    }

    private function getDocumentData(RenderContext $context, ?DocumentNode $document): ?array
    {
        if (null === $document || $document->isOrphan()) {
            return null;
        }

        $url = $this->urlGenerator->createFileUrl($context, $document->getFilePath());

        return [
            'title' => $document->getTitle()?->toString() ?? '',
            'link' => $url,
        ];
    }
}
