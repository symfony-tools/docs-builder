<?php

namespace SymfonyDocs\Directive;

use Doctrine\RST\SubDirective;

class IndexDirective extends SubDirective
{
    public function getName() : string
    {
        return 'index';
    }
}
