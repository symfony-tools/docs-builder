<?php

namespace SymfonyDocs\Directive;

use Doctrine\RST\Nodes\Node;
use Doctrine\RST\Parser;
use Doctrine\RST\SubDirective;

class ClassDirective extends SubDirective
{
    public function getName(): string
    {
        return 'class';
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
        $document->setClass($data);

        return $document;
    }
}
