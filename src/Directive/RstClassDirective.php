<?php

namespace SymfonyDocsBuilder\Directive;

use Doctrine\RST\Directives\SubDirective;
use Doctrine\RST\HTML\Directives\ClassDirective;
use Doctrine\RST\Nodes\Node;
use Doctrine\RST\Parser;

/**
 * Allows you to add custom classes to the next directive.
 */
class RstClassDirective extends SubDirective
{
    private $classDirective;

    public function __construct(ClassDirective $classDirective)
    {
        $this->classDirective = $classDirective;
    }

    /**
     * @param string[] $options
     */
    public function processSub(
        Parser $parser,
        ?Node $document,
        string $variable,
        string $data,
        array $options
    ): ?Node {
        return $this->classDirective->processSub($parser, $document, $variable, $data, $options);
    }

    public function appliesToNonBlockContent(): bool
    {
        return $this->classDirective->appliesToNonBlockContent();
    }

    public function getName() : string
    {
        return 'rst-class';
    }
}
