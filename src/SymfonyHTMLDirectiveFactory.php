<?php

namespace SymfonyDocsBuilder;

use Doctrine\RST\Directives\Admonition;
use Doctrine\RST\HTML\Directives\ClassDirective;
use Doctrine\RST\HTML\Directives\FormatDirectiveFactory;
use SymfonyDocsBuilder\CI\UrlChecker;
use SymfonyDocsBuilder\Directive as SymfonyDirectives;
use SymfonyDocsBuilder\TextRole as SymfonyTextRoles;

class SymfonyHTMLDirectiveFactory extends FormatDirectiveFactory
{
    private $buildConfig;
    private $urlChecker;

    public function __construct(BuildConfig $buildConfig, ?UrlChecker $urlChecker = null)
    {
        $this->buildConfig = $buildConfig;
        $this->urlChecker = $urlChecker;
    }

    public function getDirectives(): array
    {
        return [
            ...parent::getDirectives(),

            new Admonition('best-practice', 'Best Practice'),
            new Admonition('deprecated', 'Deprecated'),
            new Admonition('note', 'Note'),
            new Admonition('screencast', 'Screencast'),
            new Admonition('versionadded', 'Version Added'),

            new SymfonyDirectives\CodeBlockDirective(),
            new SymfonyDirectives\ConfigurationBlockDirective(),
            new SymfonyDirectives\GlossaryDirective(),
            new SymfonyDirectives\RstClassDirective(new ClassDirective()),
            new SymfonyDirectives\TopicDirective(),
        ];
    }

    public function getTextRoles(): array
    {
        return [
            ...parent::getTextRoles(),

            new SymfonyTextRoles\LiteralRole(),

            new SymfonyTextRoles\PhpClassRole($this->buildConfig->getPhpDocUrl()),
            new SymfonyTextRoles\PhpMethodRole($this->buildConfig->getPhpDocUrl()),
            new SymfonyTextRoles\PhpFunctionRole($this->buildConfig->getPhpDocUrl()),
            new SymfonyTextRoles\ClassRole($this->buildConfig->getSymfonyRepositoryUrl()),
            new SymfonyTextRoles\MethodRole($this->buildConfig->getSymfonyRepositoryUrl()),
            new SymfonyTextRoles\NamespaceRole($this->buildConfig->getSymfonyRepositoryUrl()),

            // deprecated
            new SymfonyTextRoles\SimpleRole('leader'),
            new SymfonyTextRoles\SimpleRole('merger'),
            new SymfonyTextRoles\SimpleRole('decider'),
        ];
    }
}
