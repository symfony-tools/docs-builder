<?php

namespace SymfonyDocs\Directive;

use Doctrine\RST\Nodes\ListNode;
use Doctrine\RST\Nodes\Node;
use Doctrine\RST\Nodes\ParagraphNode;
use Doctrine\RST\Parser;
use Doctrine\RST\Directives\SubDirective;

class ClassDirective extends SubDirective
{
    public function getName(): string
    {
        return 'class';
    }

    public function processSub(Parser $parser, ?Node $document, string $variable, string $data, array $options): ?Node
    {
        if (!$document instanceof ListNode && !$document instanceof ParagraphNode) {
            throw new \RuntimeException(
                sprintf(
                    "\".. class:\" directive could only be applied to paragraphs or lists (applied to \"%s\")\nTarget block:\n%s",
                    \get_class($document),
                    $document->render()
                )
            );
        }

//        $document->setClass($data);

        return $document;
    }
}
