<?php

namespace SymfonyDocs\Nodes;

use Doctrine\RST\Nodes\ListNode as Base;

class ListNode extends Base
{
    protected function createElement(string $text, string $prefix): string
    {
        return sprintf('<li>%s</li>', $text);
    }

    /**
     * @return string[]
     */
    protected function createList(bool $ordered): array
    {
        $keyword = $ordered ? 'ol' : 'ul';

        return [
            sprintf('<%s class="simple">', $keyword),
            sprintf('</%s>', $keyword),
        ];
    }
}
