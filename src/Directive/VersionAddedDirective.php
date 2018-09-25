<?php

namespace SymfonyDocs\Directive;

use Doctrine\RST\Nodes\Node;
use Doctrine\RST\Nodes\RawNode;
use Doctrine\RST\Parser;
use Doctrine\RST\SubDirective;

class VersionAddedDirective extends SubDirective
{
    public function getName(): string
    {
        return 'versionadded';
    }

    /**
     * @param string[] $options
     */
    public function processSub(
        Parser $parser,
        ?Node $document,
        string $variable,
        string $data,
        array $options
    ): ?Node {
        return new RawNode(sprintf('<div class="versionadded"><p><span class="versionmodified">New in version %s: </span>%s</p></div>', $data, trim(strip_tags((string) $document))));
    }
}
