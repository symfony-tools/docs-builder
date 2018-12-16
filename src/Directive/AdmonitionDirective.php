<?php

namespace SymfonyDocs\Directive;

use Doctrine\RST\Directives\SubDirective;
use Doctrine\RST\Nodes\Node;
use Doctrine\RST\Parser;

class AdmonitionDirective extends SubDirective
{
    public function processSub(Parser $parser, ?Node $document, string $variable, string $data, array $options): ?Node
    {
        $wrapperDiv = $parser->renderTemplate(
            'directives/admonition.html.twig',
            [
                // a bit strange, but on the old markup we literally
                // had a class of 'admonition-"
                'name' => '',
                'text' => $data,
                'class' => isset($options['class']) ? $options['class'] : null,
            ]
        );

        return $parser->getNodeFactory()->createWrapperNode($document, $wrapperDiv, '</div></div>');
    }

    public function getName(): string
    {
        return 'admonition';
    }
}
