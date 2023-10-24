<?php

namespace SymfonyTools\GuidesExtension\Node;

use phpDocumentor\Guides\Nodes\Inline\AbstractLinkInlineNode;

class ExternalLinkNode extends AbstractLinkInlineNode
{
    public function __construct(
        string $url,
        string $text,
        private string $title,
    ) {
        parent::__construct('external-link', $url, $text);
    }

    public function getTitle(): string
    {
        return $this->title;
    }
}
