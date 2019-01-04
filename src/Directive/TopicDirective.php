<?php

namespace SymfonyDocsBuilder\Directive;

use Doctrine\RST\Directives\SubDirective;
use Doctrine\RST\Nodes\Node;
use Doctrine\RST\Parser;

class TopicDirective extends SubDirective
{
    final public function processSub(Parser $parser, ?Node $document, string $variable, string $data, array $options): ?Node
    {
        $wrapperDiv = $parser->renderTemplate(
            'directives/topic.html.twig',
            [
                'name' => $data,
            ]
        );

        return $parser->getNodeFactory()->createWrapperNode($document, $wrapperDiv, '</div>');
    }

    public function getName(): string
    {
        return 'topic';
    }
}
