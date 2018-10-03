<?php

namespace SymfonyDocs\Nodes;

use Doctrine\RST\Environment;
use Doctrine\RST\Nodes\TitleNode as Base;

class TitleNode extends Base
{
    public function render(): string
    {
        $anchor = Environment::slugify((string) $this->value);

        return '<h'.$this->level.' id="'.$anchor.'">'.$this->value.'<a class="headerlink" href="#'.$anchor.'" title="Permalink to this headline">¶</a></h'.$this->level.'>';
    }
}

