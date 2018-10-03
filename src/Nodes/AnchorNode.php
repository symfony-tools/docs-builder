<?php

namespace SymfonyDocs\Nodes;

use Doctrine\RST\Nodes\AnchorNode as Base;

class AnchorNode extends Base
{
    public function render(): string
    {
        return '<span id="'.$this->value.'"></span>';
    }
}

