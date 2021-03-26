<?php

declare(strict_types=1);

/*
 * This file is part of the Docs Builder package.
 * (c) Ryan Weaver <ryan@symfonycasts.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SymfonyDocsBuilder\Renderers;

use Doctrine\RST\Nodes\CodeNode;
use Doctrine\RST\Renderers\NodeRenderer;
use Doctrine\RST\Templates\TemplateRenderer;
use Highlight\Highlighter;
use function Symfony\Component\String\u;

class CodeNodeRenderer implements NodeRenderer
{
    private static $isHighlighterConfigured = false;

    private const LANGUAGES_MAPPING = [
        'env' => 'bash',
        'html+jinja' => 'twig',
        'html+twig' => 'twig',
        'jinja' => 'twig',
        'html+php' => 'html',
        'xml+php' => 'xml',
        'php-annotations' => 'php',
        'php-attributes' => 'php',
        'terminal' => 'bash',
        'rst' => 'markdown',
        'php-standalone' => 'php',
        'php-symfony' => 'php',
        'varnish4' => 'c',
        'varnish3' => 'c',
        'vcl' => 'c',
    ];

    /** @var CodeNode */
    private $codeNode;

    /** @var TemplateRenderer */
    private $templateRenderer;

    public function __construct(CodeNode $codeNode, TemplateRenderer $templateRenderer)
    {
        $this->codeNode = $codeNode;
        $this->templateRenderer = $templateRenderer;
    }

    public function render(): string
    {
        $code = trim($this->codeNode->getValue());
        if ($this->codeNode->isRaw()) {
            return $code;
        }

        $language = $this->codeNode->getLanguage() ?? 'php';
        $languageMapping = self::LANGUAGES_MAPPING[$language] ?? $language;
        $languages = array_unique([$language, $languageMapping]);

        if ('text' === $language) {
            $highlightedCode = $code;
        } else {
            $this->configureHighlighter();

            $highLighter = new Highlighter();
            $highlightedCode = $highLighter->highlight($languageMapping, $code)->value;

            // this allows to highlight the $ in PHP variable names
            $highlightedCode = str_replace('<span class="hljs-variable">$', '<span class="hljs-variable"><span class="hljs-variable-other-marker">$</span>', $highlightedCode);
        }

        if ('terminal' === $language) {
            $highlightedCode = preg_replace('/^\$/m', '<span class="hljs-prompt">$</span>', $highlightedCode);
            $highlightedCode = preg_replace('/^C:\\\&gt;/m', '<span class="hljs-prompt">C:\&gt;</span>', $highlightedCode);
        }

        $numOfLines = \count(preg_split('/\r\n|\r|\n/', $highlightedCode));
        $lines = implode("\n", range(1, $numOfLines));

        return $this->templateRenderer->render(
            'code.html.twig',
            [
                'languages' => $languages,
                'lines' => $lines,
                'code' => $highlightedCode,
                // this is the number of digits of the codeblock lines-of-code
                // e.g. LOC = 5, digits = 1; LOC = 18, digits = 2
                // this is useful to tweak the code listings according to their length
                'numLocDigits' => strlen((string) $numOfLines),
            ]
        );
    }

    public static function isLanguageSupported(string $lang): bool
    {
        $highlighter = new Highlighter();
        $supportedLanguages = array_merge(
            array_keys(self::LANGUAGES_MAPPING),
            $highlighter->listRegisteredLanguages(true),
            // not highlighted, but valid
            ['text']
        );

        return \in_array($lang, $supportedLanguages, true);
    }

    private function getLines(string $code): array
    {
        $lines = preg_split('/\r\n|\r|\n/', $code);
        $reversedLines = array_reverse($lines);

        // trim empty lines at the end of the code
        foreach ($reversedLines as $key => $line) {
            if ('' !== trim($line)) {
                break;
            }

            unset($reversedLines[$key]);
        }

        return array_reverse($reversedLines);
    }

    private function configureHighlighter()
    {
        if (false === self::$isHighlighterConfigured) {
            Highlighter::registerLanguage('php', __DIR__.'/../Templates/highlight.php/php.json', true);
            Highlighter::registerLanguage('twig', __DIR__.'/../Templates/highlight.php/twig.json', true);
        }

        self::$isHighlighterConfigured = true;
    }
}
