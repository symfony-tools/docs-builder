<?php

namespace SymfonyDocsBuilder\Directive;

class CautionDirective extends AbstractAdmonitionDirective
{
    public function __construct()
    {
        parent::__construct('caution', 'Caution');
    }
}
