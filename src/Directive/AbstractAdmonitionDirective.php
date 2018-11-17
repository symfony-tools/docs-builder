<?php

namespace SymfonyDocs\Directive;

use Doctrine\RST\Nodes\Node;
use Doctrine\RST\Nodes\WrapperNode;
use Doctrine\RST\Parser;
use Doctrine\RST\Directives\SubDirective;

abstract class AbstractAdmonitionDirective extends SubDirective
{
    /** @var string */
    private $name;

    /** @var string */
    private $text;

    public function __construct(string $name, string $text)
    {
        $this->name = $name;
        $this->text = $text;
    }

    final public function processSub(Parser $parser, ?Node $document, string $variable, string $data, array $options): ?Node
    {
        return new WrapperNode(
            $document,
            sprintf(
                '<div class="admonition-%s admonition-wrapper"><div class="%s"></div><div class="admonition admonition-%s"><p class="admonition-title">%s</p>',
                $this->name,
                $this->name,
                $this->name,
                $this->text
            ),
            '</div></div>'
        );
    }

    final public function getName(): string
    {
        return $this->name;
    }
}
