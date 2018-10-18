<?php

namespace SymfonyDocs\Directive;

class TipDirective extends AbstractAdmonitionDirective
{
    public function __construct()
    {
        parent::__construct('tip', 'Tip');
    }
}
