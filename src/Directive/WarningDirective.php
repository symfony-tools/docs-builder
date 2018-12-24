<?php

namespace SymfonyDocs\Directive;

class WarningDirective extends AbstractAdmonitionDirective
{
    public function __construct()
    {
        // we render warning and caution the same
        parent::__construct('warning', 'Warning');
    }
}
