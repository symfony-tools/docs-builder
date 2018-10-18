<?php

namespace SymfonyDocs\Directive;

use Doctrine\RST\Nodes\Node;
use Doctrine\RST\Nodes\WrapperNode;
use Doctrine\RST\Parser;
use Doctrine\RST\SubDirective;

class SidebarDirective extends SubDirective
{
    public function getName(): string
    {
        return 'sidebar';
    }

    public function processSub(Parser $parser, ?Node $document, string $variable, string $data, array $options): ?Node
    {
        return new WrapperNode(
            $document,
            sprintf('<div class="admonition-wrapper"><div class="sidebar"></div><div class="admonition admonition-sidebar"><p class="sidebar-title">%s</p>', $data),
            '</div></div>'
        );
    }
}
