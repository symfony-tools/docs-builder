<?php

namespace SymfonyDocs\Nodes;

use Doctrine\RST\Nodes\AnchorNode as Base;

class AnchorNode extends Base
{
    public function doRender(): string
    {
        return sprintf('<span id="%s"></span>', $this->value);
    }
}

