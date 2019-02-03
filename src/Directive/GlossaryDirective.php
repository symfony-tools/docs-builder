<?php

namespace SymfonyDocsBuilder\Directive;

use Doctrine\RST\Directives\SubDirective;

/**
 * @deprecated
 */
class GlossaryDirective extends SubDirective
{
    public function getName() : string
    {
        return 'glossary';
    }
}
