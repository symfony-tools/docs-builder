<?php

namespace SymfonyDocs;

use Doctrine\RST\Directive;
use Doctrine\RST\Factory;
use Doctrine\RST\HTML\Kernel;
use Highlight\Highlighter;
use SymfonyDocs\CodeBlock\CodeBlockConsoleRenderer;
use SymfonyDocs\CodeBlock\CodeBlockLanguageDetector;
use SymfonyDocs\CodeBlock\CodeBlockRenderer;
use SymfonyDocs\CodeBlock\CodeBlockWithLineNumbersRenderer;
use SymfonyDocs\Directive\CautionDirective;
use SymfonyDocs\Directive\ClassDirective;
use SymfonyDocs\Directive\CodeBlockDirective;
use SymfonyDocs\Directive\ConfigurationBlockDirective;
use SymfonyDocs\Directive\IndexDirective;
use SymfonyDocs\Directive\NoteDirective;
use SymfonyDocs\Directive\RoleDirective;
use SymfonyDocs\Directive\SeeAlsoDirective;
use SymfonyDocs\Directive\SidebarDirective;
use SymfonyDocs\Directive\TipDirective;
use SymfonyDocs\Directive\TocDirective;
use SymfonyDocs\Directive\VersionAddedDirective;
use SymfonyDocs\Reference\ClassReference;
use SymfonyDocs\Reference\MethodReference;
use SymfonyDocs\Reference\NamespaceReference;
use SymfonyDocs\Reference\PhpFunctionReference;
use SymfonyDocs\Reference\PhpMethodReference;

class HtmlKernel extends Kernel
{
    /** @var NodeFactory */
    private $symfonyDocsFactory;

    public function getName(): string
    {
        return parent::getName();
    }

    /**
     * @param Directive[] $directives
     */
    public function __construct(array $directives = [])
    {
        parent::__construct($directives);

        $this->symfonyDocsFactory = new NodeFactory($this->getName());
    }

    public function getFactory() :Factory
    {
        return $this->symfonyDocsFactory;
    }

    public function getDirectives(): array
    {
        $directives = parent::getDirectives();

        return array_merge($directives, [
            new CautionDirective(),
            new ClassDirective(),
            new CodeBlockDirective(),
            new ConfigurationBlockDirective(),
            new IndexDirective(),
            new NoteDirective(),
            new RoleDirective(),
            new SeeAlsoDirective(),
            new SidebarDirective(),
            new TipDirective(),
            new TocDirective(),
            new VersionAddedDirective(),
        ]);
    }

    public function getReferences(): array
    {
        $references = parent::getReferences();

        return array_merge($references, [
            new ClassReference(),
            new MethodReference(),
            new NamespaceReference(),
            new PhpFunctionReference(),
            new PhpMethodReference(),
        ]);
    }
}