<?php

namespace SymfonyDocs\Directive;

use Doctrine\RST\Directives\SubDirective;

class RoleDirective extends SubDirective
{
    public function getName() : string
    {
        return 'role';
    }
}
