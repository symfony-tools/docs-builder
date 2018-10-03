<?php

namespace SymfonyDocs;

use Doctrine\RST\Directive;
use Doctrine\RST\Factory;
use Doctrine\RST\HTML\Kernel;
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
use SymfonyDocs\Directive\VersionAddedDirective;
use SymfonyDocs\Reference\ClassReference;
use SymfonyDocs\Reference\DocReference;
use SymfonyDocs\Reference\MethodReference;
use SymfonyDocs\Reference\NamespaceReference;
use SymfonyDocs\Reference\PhpClassReference;
use SymfonyDocs\Reference\PhpFunctionReference;
use SymfonyDocs\Reference\PhpMethodReference;
use SymfonyDocs\Reference\RefReference;

class HtmlKernel extends Kernel
{
    /** @var NodeFactory */
    private $symfonyDocsFactory;

    /** @var array */
    private static $configuration;

    public static function getConfiguration(): array
    {
        if (null === self::$configuration) {
            self::$configuration = json_decode(file_get_contents(__DIR__.'/../conf.json'), true);
        }

        return self::$configuration;
    }

    public static function getVersion(): string
    {
        if (!isset(self::getConfiguration()['version'])) {
            throw new \RuntimeException('The version must be defined in "/_build/conf.json"');
        }

        return self::getConfiguration()['version'];
    }

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

    public function getFactory(): Factory
    {
        return $this->symfonyDocsFactory;
    }

    public function getDirectives(): array
    {
        $directives = parent::getDirectives();

        return array_merge(
            $directives,
            [
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
                new VersionAddedDirective(),
            ]
        );
    }

    public function getReferences(): array
    {
        return [
            new DocReference(),
            new RefReference(),
            new ClassReference(),
            new MethodReference(),
            new NamespaceReference(),
            new PhpFunctionReference(),
            new PhpMethodReference(),
            new PhpClassReference(),
        ];
    }
}