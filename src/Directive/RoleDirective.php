<?php

namespace SymfonyDocs\Directive;

use Doctrine\RST\Nodes\Node;
use Doctrine\RST\Nodes\WrapperNode;
use Doctrine\RST\Parser;
use Doctrine\RST\Directives\SubDirective;

class RoleDirective extends SubDirective
{
    public function getName(): string
    {
        return 'role';
    }

    public function processSub(Parser $parser, ?Node $document, string $variable, string $data, array $options): ?Node
    {
        return new WrapperNode($document, '<div class="role">', '</div>');
    }
}
