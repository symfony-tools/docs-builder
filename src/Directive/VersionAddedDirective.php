<?php

namespace SymfonyDocs\Directive;

use Doctrine\RST\Nodes\Node;
use Doctrine\RST\Nodes\WrapperNode;
use Doctrine\RST\Parser;
use Doctrine\RST\Directives\SubDirective;

class VersionAddedDirective extends SubDirective
{
    public function getName(): string
    {
        return 'versionadded';
    }

    public function processSub(Parser $parser, ?Node $document, string $variable, string $data, array $options): ?Node
    {
        return new WrapperNode(
            $document,
            sprintf('<div class="versionadded"><div><span class="versionmodified">New in version %s: </span>', $data),
            '</div></div>'
        );
    }
}
