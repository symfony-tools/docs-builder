<?php

namespace SymfonyTools\GuidesExtension\Node;

use phpDocumentor\Guides\Nodes\Inline\AbstractLinkInlineNode;

class ExternalLinkNode extends AbstractLinkInlineNode
{
    public function __construct(
        private string $url,
        string $text,
        private string $title,
    ) {
        parent::__construct('external-link', $text);
    }

    public function getUrl(): string
    {
        return $this->url;
    }

    public function getText(): string
    {
        return $this->getValue();
    }

    public function getTitle(): string
    {
        return $this->title;
    }
}
