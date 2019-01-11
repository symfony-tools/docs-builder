<?php

namespace SymfonyDocsBuilder\Code;

use Doctrine\RST\Nodes\DocumentNode;
use Doctrine\RST\Nodes\Node;

class BuildableDocument
{
    private $filename;

    private $nodes;

    /**
     * @param string $filename
     * @param Node[] $nodes
     */
    public function __construct(string $filename, array $nodes)
    {
        $this->filename = $filename;
        $this->nodes = $nodes;
    }

    public function getFilename(): string
    {
        return $this->filename;
    }

    /**
     * @return Node[]
     */
    public function getNodes(): array
    {
        return $this->nodes;
    }
}