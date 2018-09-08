<?php

namespace SymfonyDocs\Directive;

use Doctrine\RST\Nodes\Node;
use Doctrine\RST\Nodes\WrapperNode;
use Doctrine\RST\Parser;
use Doctrine\RST\SubDirective;

class ClassDirective extends SubDirective
{
    public function getName() : string
    {
        return 'class';
    }

    // TODO - see framework.rst config reference
}
