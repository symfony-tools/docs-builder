<?php

namespace SymfonyTools\GuidesExtension\Node;

use phpDocumentor\Guides\Nodes\AbstractNode;

class ConfigurationBlockNode extends AbstractNode
{
    /** @param list<ConfigurationTabNode> $tabs */
    public function __construct(
        public readonly array $tabs
    ) {
    }
}
