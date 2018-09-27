<?php

namespace SymfonyDocs;

use Doctrine\RST\Factory;
use Doctrine\RST\Nodes\ListNode;
use SymfonyDocs\Nodes\ListNode as SymfonyListNode;
use Doctrine\RST\Nodes\Node;
use Doctrine\RST\Nodes\CodeNode;
use SymfonyDocs\Nodes\CodeNode as SymfonyNode;

class NodeFactory extends Factory
{
    /**
     * @param Node|string|null $value
     */
    public function createListNode($value = null) : ListNode
    {
        return new SymfonyListNode($value);
    }

    /**
     * @param string[] $lines
     */
    public function createCodeNode(array $lines) : CodeNode
    {
        return new SymfonyNode($lines);
    }
}
