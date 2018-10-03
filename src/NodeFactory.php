<?php

namespace SymfonyDocs;

use Doctrine\RST\Environment;
use Doctrine\RST\Factory;
use Doctrine\RST\Nodes\AnchorNode;
use Doctrine\RST\Nodes\CodeNode;
use Doctrine\RST\Nodes\ListNode;
use Doctrine\RST\Nodes\Node;
use Doctrine\RST\Nodes\TitleNode;
use Doctrine\RST\Nodes\TableNode;
use Doctrine\RST\Nodes\TocNode;
use Doctrine\RST\Parser;
use Doctrine\RST\Parser\LineChecker;
use Doctrine\RST\Span;
use SymfonyDocs\Nodes\AnchorNode as SymfonyAnchorNode;
use SymfonyDocs\Nodes\CodeNode as SymfonyCodeNode;
use SymfonyDocs\Nodes\ListNode as SymfonyListNode;
use SymfonyDocs\Nodes\TitleNode as SymfonyTitleNode;
use SymfonyDocs\Nodes\TocNode as SymfonyTocNode;
use SymfonyDocs\Nodes\SpanNode as SymfonySpanNode;
use SymfonyDocs\Nodes\TableNode as SymfonyTableNode;

class NodeFactory extends Factory
{
    public function createListNode($value = null): ListNode
    {
        return new SymfonyListNode($value);
    }

    public function createCodeNode(array $lines): CodeNode
    {
        return new SymfonyCodeNode($lines);
    }

    public function createTocNode(Environment $environment, array $files, array $options): TocNode
    {
        return new SymfonyTocNode($environment, $files, $options);
    }

    public function createTitleNode(Node $value, int $level, string $token): TitleNode
    {
        return new SymfonyTitleNode($value, $level, $token);
    }

    public function createAnchorNode($value = null): AnchorNode
    {
        return new SymfonyAnchorNode($value);
    }

    public function createSpan(Parser $parser, $span): Span
    {
        return new SymfonySpanNode($parser, $span);
    }

    public function createTableNode(array $parts, string $type, LineChecker $lineChecker) : TableNode
    {
        return new SymfonyTableNode($parts, $type, $lineChecker);
    }
}
