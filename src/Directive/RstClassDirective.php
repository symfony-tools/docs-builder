<?php

namespace SymfonyDocsBuilder\Directive;

use Doctrine\RST\HTML\Directives\ClassDirective;

/**
 * Allows you to add custom classes to the next directive.
 */
class RstClassDirective extends ClassDirective
{
    public function getName() : string
    {
        return 'rst-class';
    }
}
