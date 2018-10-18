<?php

namespace SymfonyDocs\Nodes;

use Doctrine\RST\Nodes\Node;
use Doctrine\RST\Nodes\ParagraphNode as Base;
use function trim;

class ParagraphNode extends Base
{
    /** @var string */
    private $class = '';

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

    public function render() : string
    {
        if ($this->value instanceof Node) {
            $text = trim($this->value->render());
        } else {
            $text = trim($this->value);
        }

        if ($text !== '') {
            if ($this->class) {
                return sprintf('<p class="%s">%s</p>', $this->class, $text);
            }

            return '<p>' . $text . '</p>';
        }

        return '';
    }
}
