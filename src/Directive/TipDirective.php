<?php

namespace SymfonyDocs\Directive;

use Doctrine\RST\Nodes\Node;
use Doctrine\RST\Nodes\RawNode;
use Doctrine\RST\Nodes\WrapperNode;
use Doctrine\RST\Parser;
use Doctrine\RST\SubDirective;

class TipDirective extends SubDirective
{
    public function getName() : string
    {
        return 'tip';
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
        return new WrapperNode($document, '<div class="admonition-wrapper"><div class="tip"></div><div class="admonition admonition-tip"><p class="first admonition-title">Tip</p>', '</div></div>');
    }
}
