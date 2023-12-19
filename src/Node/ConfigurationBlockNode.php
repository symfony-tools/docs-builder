<?php

/*
 * This file is part of the Guides SymfonyExtension package.
 *
 * (c) Wouter de Jong
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SymfonyTools\GuidesExtension\Node;

use phpDocumentor\Guides\Nodes\AbstractNode;

/** @extends AbstractNode<list<ConfigurationTab>> */
class ConfigurationBlockNode extends AbstractNode
{
    /** @param list<ConfigurationTab> $tabs */
    public function __construct(
        array $tabs
    ) {
        $this->value = $tabs;
    }

    /** @return list<ConfigurationTab> */
    public function getTabs(): array
    {
        return $this->value;
    }
}
