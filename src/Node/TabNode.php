<?php

namespace SymfonyDocsBuilder\Node;

use Doctrine\RST\Nodes\Node;

/**
 * Wraps nodes + options in a TabDirective.
 */
class TabNode extends Node
{
    /**
     * @var Node[]
     */
    private array $nodes;

    private string $tabName;

    public function __construct(array $nodes, string $tabName)
    {
        $this->nodes = $nodes;
        $this->tabName = $tabName;

        parent::__construct();
    }

    public function getNodes(): array
    {
        return $this->nodes;
    }

    public function getTabName(): string
    {
        return $this->tabName;
    }

    public function getSluggedTabName(): string
    {
        return strtolower(str_replace(' ', '-', $this->tabName));
    }
}
