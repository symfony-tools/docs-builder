<?php

namespace SymfonyDocs\Nodes;

use Doctrine\RST\Environment;
use Doctrine\RST\Nodes\TitleNode as Base;

class TitleNode extends Base
{
    public function doRender(): string
    {
        $anchor = Environment::slugify((string) $this->value);

        return sprintf(
            '<h%s id="%s">%s<a class="headerlink" href="#%s" title="Permalink to this headline">¶</a></h%s>',
            $this->level,
            $anchor,
            $this->value,
            $anchor,
            $this->level
        );
    }
}

