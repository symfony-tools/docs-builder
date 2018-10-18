<?php

namespace SymfonyDocs;

use Doctrine\RST\DefaultNodeFactory;
use Doctrine\RST\HTML\Document;
use Doctrine\RST\HTML\Kernel;
use Doctrine\RST\HTML\Nodes as ParserNodes;
use Doctrine\RST\NodeFactory as NodeFactoryInterface;
use Doctrine\RST\NodeInstantiator;
use Doctrine\RST\NodeTypes;
use SymfonyDocs\Directive as SymfonyDoirectives;
use SymfonyDocs\Nodes as SymfonyNodes;
use SymfonyDocs\Reference as SymfonyRefernces;

class HtmlKernel extends Kernel
{
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

    public static function getSymfonyApiUrl(): string
    {
        if (!isset(self::getConfiguration()['symfony_api_url'])) {
            throw new \RuntimeException('The "symfony_api_url" must be defined in "/_build/conf.json"');
        }

        return self::getConfiguration()['symfony_api_url'];
    }

    public static function getPhpDocUrl(): string
    {
        if (!isset(self::getConfiguration()['php_doc_url'])) {
            throw new \RuntimeException('The "php_doc_url" must be defined in "/_build/conf.json"');
        }

        return self::getConfiguration()['php_doc_url'];
    }

    public function getDirectives(): array
    {
        $directives = parent::getDirectives();

        return array_merge(
            $directives,
            [
                new SymfonyDoirectives\CautionDirective(),
                new SymfonyDoirectives\ClassDirective(),
                new SymfonyDoirectives\CodeBlockDirective(),
                new SymfonyDoirectives\ConfigurationBlockDirective(),
                new SymfonyDoirectives\IndexDirective(),
                new SymfonyDoirectives\NoteDirective(),
                new SymfonyDoirectives\RoleDirective(),
                new SymfonyDoirectives\SeeAlsoDirective(),
                new SymfonyDoirectives\SidebarDirective(),
                new SymfonyDoirectives\TipDirective(),
                new SymfonyDoirectives\VersionAddedDirective(),
                new SymfonyDoirectives\BestPracticeDirective(),
            ]
        );
    }

    public function getReferences(): array
    {
        return [
            new SymfonyRefernces\DocReference(),
            new SymfonyRefernces\RefReference(),
            new SymfonyRefernces\ClassReference(),
            new SymfonyRefernces\MethodReference(),
            new SymfonyRefernces\NamespaceReference(),
            new SymfonyRefernces\PhpFunctionReference(),
            new SymfonyRefernces\PhpMethodReference(),
            new SymfonyRefernces\PhpClassReference(),
        ];
    }

    protected function createNodeFactory(): NodeFactoryInterface
    {
        return new DefaultNodeFactory(
            new NodeInstantiator(NodeTypes::ANCHOR, SymfonyNodes\AnchorNode::class),
            new NodeInstantiator(NodeTypes::CODE, SymfonyNodes\CodeNode::class),
            new NodeInstantiator(NodeTypes::LIST, SymfonyNodes\ListNode::class),
            new NodeInstantiator(NodeTypes::PARAGRAPH, SymfonyNodes\ParagraphNode::class),
            new NodeInstantiator(NodeTypes::SPAN, SymfonyNodes\SpanNode::class),
            new NodeInstantiator(NodeTypes::TABLE, SymfonyNodes\TableNode::class),
            new NodeInstantiator(NodeTypes::TITLE, SymfonyNodes\TitleNode::class),
            new NodeInstantiator(NodeTypes::TOC, SymfonyNodes\TocNode::class),
            new NodeInstantiator(NodeTypes::DOCUMENT, Document::class),
            new NodeInstantiator(NodeTypes::SEPARATOR, ParserNodes\SeparatorNode::class),
            new NodeInstantiator(NodeTypes::QUOTE, ParserNodes\QuoteNode::class)
        );
    }
}
