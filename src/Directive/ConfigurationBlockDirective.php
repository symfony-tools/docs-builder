<?php

namespace SymfonyDocs\Directive;

use Doctrine\RST\Directives\SubDirective;
use Doctrine\RST\Nodes\CodeNode;
use Doctrine\RST\Nodes\Node;
use Doctrine\RST\Parser;
use function strtoupper;

class ConfigurationBlockDirective extends SubDirective
{
    public function getName(): string
    {
        return 'configuration-block';
    }

    public function processSub(Parser $parser, ?Node $document, string $variable, string $data, array $options): ?Node
    {
        $blocks = [];
        foreach ($document->getNodes() as $node) {
            if (!$node instanceof CodeNode) {
                continue;
            }

            $language = $node->getLanguage() ?? 'Unknown';

            $blocks[] = [
                'language' => strtoupper($language),
                'code'     => $node->render(),
            ];
        }

        $wrapperDiv = $parser->renderTemplate(
            'directives/configuration-block.html.twig',
            [
                'blocks' => $blocks,
            ]
        );

        return $parser->getNodeFactory()->createWrapperNode(null, $wrapperDiv, '</div>');
    }
}
