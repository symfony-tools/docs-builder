<?php

namespace SymfonyDocs;

use Doctrine\RST\Environment;
use Doctrine\RST\Factory;
use Doctrine\RST\Nodes\CodeNode;
use Doctrine\RST\Nodes\ListNode;
use Doctrine\RST\Nodes\TocNode;
use Doctrine\RST\Nodes\Node;
use SymfonyDocs\Nodes\CodeNode as SymfonyCodeNode;
use SymfonyDocs\Nodes\ListNode as SymfonyListNode;
use SymfonyDocs\Nodes\TocNode as SymfonyTocNode;

class NodeFactory extends Factory
{
    /**
     * @param Node|string|null $value
     */
    public function createListNode($value = null): ListNode
    {
        return new SymfonyListNode($value);
    }

    /**
     * @param string[] $lines
     */
    public function createCodeNode(array $lines): CodeNode
    {
        return new SymfonyCodeNode($lines);
    }

    /**
     * @param string[] $files
     * @param string[] $options
     */
    public function createTocNode(Environment $environment, array $files, array $options): TocNode
    {
        return new SymfonyTocNode($environment, $files, $options);
    }
}
