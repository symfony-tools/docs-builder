<?php

namespace SymfonyDocs\Directive;

use Doctrine\RST\Directives\SubDirective;

class IndexDirective extends SubDirective
{
    public function getName() : string
    {
        return 'index';
    }
}
