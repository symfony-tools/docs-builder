<?php

namespace SymfonyDocs\Directive;

use Doctrine\RST\Directives\SubDirective;
use Doctrine\RST\Nodes\Node;
use Doctrine\RST\Parser;

class VersionAddedDirective extends SubDirective
{
    public function getName(): string
    {
        return 'versionadded';
    }

    public function processSub(Parser $parser, ?Node $document, string $variable, string $data, array $options): ?Node
    {
        $wrapperDiv = $parser->renderTemplate(
            'directives/version-added.html.twig',
            [
                'version' => $data,
            ]
        );

        return $parser->getNodeFactory()->createWrapperNode($document, $wrapperDiv, '</div></div>');
    }
}
