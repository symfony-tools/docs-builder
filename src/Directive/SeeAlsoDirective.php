<?php

namespace SymfonyDocs\Directive;

class SeeAlsoDirective extends AbstractAdmonitionDirective
{
    public function __construct()
    {
        parent::__construct('seealso', 'See also');
    }
}
