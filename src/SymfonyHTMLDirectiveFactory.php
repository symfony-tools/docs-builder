<?php

namespace SymfonyDocsBuilder;

use Doctrine\RST\HTML\Directives\ClassDirective;
use Doctrine\RST\HTML\Directives\FormatDirectiveFactory;
use Doctrine\RST\HTML\TextRoles\DocTextRole;
use Doctrine\RST\HTML\TextRoles\LinkTextRole;
use Doctrine\RST\TextRoles\DefinitionTextRole;
use Doctrine\RST\TextRoles\WrapperTextRole;
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
            new SymfonyDirectives\AdmonitionDirective(),
            new SymfonyDirectives\AttentionDirective(),
            new SymfonyDirectives\CautionDirective(),
            new SymfonyDirectives\CodeBlockDirective(),
            new SymfonyDirectives\ConfigurationBlockDirective(),
            new SymfonyDirectives\DangerDirective(),
            new SymfonyDirectives\DeprecatedDirective(),
            new SymfonyDirectives\ErrorDirective(),
            new SymfonyDirectives\FigureDirective(),
            new SymfonyDirectives\HintDirective(),
            new SymfonyDirectives\ImportantDirective(),
            new SymfonyDirectives\IndexDirective(),
            new SymfonyDirectives\RoleDirective(),
            new SymfonyDirectives\NoteDirective(),
            new SymfonyDirectives\RstClassDirective(new ClassDirective()),
            new SymfonyDirectives\ScreencastDirective(),
            new SymfonyDirectives\SeeAlsoDirective(),
            new SymfonyDirectives\SidebarDirective(),
            new SymfonyDirectives\TipDirective(),
            new SymfonyDirectives\TopicDirective(),
            new SymfonyDirectives\WarningDirective(),
            new SymfonyDirectives\VersionAddedDirective(),
            new SymfonyDirectives\BestPracticeDirective(),
            new SymfonyDirectives\GlossaryDirective(),
        ];
    }

    public function getTextRoles(): array
    {
        return [
            new DocTextRole(),
            new DocTextRole('ref', true),
            new WrapperTextRole('aspect'),
            new WrapperTextRole('command'),
            new WrapperTextRole('dfn'),
            new WrapperTextRole('file'),
            new WrapperTextRole('guilabel'),
            new WrapperTextRole('kbd'),
            new WrapperTextRole('mailheader'),
            new WrapperTextRole('math'),
            new WrapperTextRole('subscript', null, ['sub']),
            new WrapperTextRole('superscript', null, ['sup']),
            new WrapperTextRole('title-reference', null, ['t', 'title']),
            new DefinitionTextRole('abbreviation', null, ['abbr']),

            new SymfonyTextRoles\LinkRole($this->urlChecker),
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
