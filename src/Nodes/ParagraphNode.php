<?php

namespace SymfonyDocs\Nodes;

use Doctrine\RST\Nodes\Node;
use Doctrine\RST\Nodes\ParagraphNode as Base;
use function trim;

class ParagraphNode extends Base
{
    use ClassTrait;

    public function doRender(): string
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

            return sprintf('<p>%s</p>', $text);
        }

        return '';
    }
}
