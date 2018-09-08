<?php

namespace SymfonyDocs\Directive;

use Doctrine\RST\Nodes\Node;
use Doctrine\RST\Nodes\WrapperNode;
use Doctrine\RST\Parser;
use Doctrine\RST\SubDirective;

class SeeAlsoDirective extends SubDirective
{
    public function getName() : string
    {
        return 'seealso';
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

        // TODO - this is currently "tip"
        return new WrapperNode($document, '<div class="alert tip bg-success text-light"><i class="fas fa-question-circle mr-2"></i>', '</div>');
    }
}
