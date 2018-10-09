<?php

namespace SymfonyDocs\Nodes;

use Doctrine\RST\Nodes\ListNode as Base;

class ListNode extends Base
{
    /** @var string */
    private $class = '';

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
            sprintf('<%s class="%s">', $keyword, trim('simple '.$this->class)),
            sprintf('</%s>', $keyword),
        ];
    }

    /**
     * @return string
     */
    public function getClass(): ?string
    {
        return $this->class;
    }

    /**
     * @param string $class
     *
     * @return $this
     */
    public function setClass(?string $class)
    {
        $this->class = $class;

        return $this;
    }
}
