<?php

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
