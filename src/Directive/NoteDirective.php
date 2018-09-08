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
        return new WrapperNode($document, '<div class="alert note bg-light-yellow text-dark border"><i class="fas fa-sticky-note text-primary mr-2"></i>', '</div>');
    }
}
