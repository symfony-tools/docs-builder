<?php

namespace SymfonyDocs\Nodes;

use Doctrine\RST\Nodes\ListNode as Base;

class ListNode extends Base
{
    use ClassTrait;

    protected function createElement(string $text, string $prefix): string
    {
        return sprintf('<li>%s</li>', $text);
    }

    protected function createList(bool $ordered): array
    {
        $keyword = $ordered ? 'ol' : 'ul';

        return [
            sprintf('<%s class="%s">', $keyword, trim('simple '.$this->class)),
            sprintf('</%s>', $keyword),
        ];
    }
}
