<?php

namespace SymfonyDocs\Directive;

use Doctrine\RST\Nodes\Node;
use Doctrine\RST\Nodes\WrapperNode;
use Doctrine\RST\Parser;
use Doctrine\RST\SubDirective;

class NoteDirective extends SubDirective
{
    public function getName() : string
    {
        return 'note';
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
    ) : ?Node {
        return new WrapperNode($document, '<div class="admonition-wrapper"><div class="note"></div><div class="admonition admonition-note"><p class="admonition-title">Note</p>', '</div></div>');
    }
}
