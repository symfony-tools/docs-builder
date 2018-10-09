<?php

namespace SymfonyDocs\Directive;

use Doctrine\RST\Nodes\Node;
use Doctrine\RST\Nodes\WrapperNode;
use Doctrine\RST\Parser;
use Doctrine\RST\SubDirective;

class BestPracticeDirective extends SubDirective
{
    public function getName(): string
    {
        return 'best-practice';
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
        return new WrapperNode(
            $document,
            '<div class="admonition-best-practice admonition-wrapper"><div class="best-practice"></div><div class="admonition admonition-best-practice"><p class="admonition-title">Best Practice</p>',
            '</div></div>'
        );
    }
}
