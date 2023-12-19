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

use phpDocumentor\Guides\Nodes\Node;

final class ConfigurationTab
{
    public readonly string $hash;

    public function __construct(
        public readonly string $label,
        public readonly Node $content,
    ) {
        $this->hash = hash('sha1', $content->getValue());
    }
}
