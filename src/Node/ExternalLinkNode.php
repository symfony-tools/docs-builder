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

use phpDocumentor\Guides\Nodes\Inline\AbstractLinkInlineNode;
use phpDocumentor\Guides\Nodes\Inline\PlainTextInlineNode;

final class ExternalLinkNode extends AbstractLinkInlineNode
{
    public function __construct(
        string $url,
        string $text,
        private string $title,
    ) {
        parent::__construct('external-link', $url, [new PlainTextInlineNode($text)]);
    }

    public function getTitle(): string
    {
        return $this->title;
    }
}
