<?php

namespace SymfonyTools\GuidesExtension\Highlighter;

use Highlight\Highlighter as HighlightPHP;
use Highlight\HighlightResult as HighlightPHPResult;
use Psr\Log\LoggerInterface;

final class Highlighter
{
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

    public function __construct(
        private HighlightPHP $highlighter,
        private LoggerInterface $logger
    ) {
    }

    public function highlight(string $language, string $code): HighlightResult
    {
        if ('text' === $language) {
            // Highlighter escapes correctly the code, we need to manually escape only for "text" code
            $code = $this->escapeForbiddenCharactersInsideCodeBlock($code);

            return new HighlightResult('text', $code);
        }

        try {
            $highlight = $this->highlighter->highlight(self::LANGUAGES_MAPPING[$language] ?? $language, $code);

            // this allows to highlight the $ in PHP variable names
            $highlight->value = str_replace('<span class="hljs-variable">$', '<span class="hljs-variable"><span class="hljs-variable-other-marker">$</span>', $highlight->value);

            if ('terminal' === $language) {
                $highlight->value = preg_replace('/^\$ /m', '<span class="hljs-prompt">$ </span>', $highlight->value);
                $highlight->value = preg_replace('/^C:\\\&gt; /m', '<span class="hljs-prompt">C:\&gt; </span>', $highlight->value);
            }

            $highlightLanguage = $highlight->language;
            if ('xml' === $highlightLanguage && str_contains($language, 'html')) {
                $highlightLanguage = 'html';
            }

            return new HighlightResult($highlightLanguage, $highlight->value);
        } catch (\Throwable $e) {
            $this->logger->error('Error highlighting {language} code block', [
                'language' => $language,
                'code' => $code,
                'error' => $e,
            ]);

            return new HighlightResult($language, $code);
        }
    }

    /**
     * Code blocks are displayed in "<pre>" tags, which has some reserved characters:
     * https://developer.mozilla.org/en-US/docs/Web/HTML/Element/pre
     */
    private function escapeForbiddenCharactersInsideCodeBlock(string $code): string
    {
        $codeEscaped = preg_replace('/&(?!amp;|lt;|gt;|quot;)/', '&amp;', $code);

        return strtr($codeEscaped, ['<' => '&lt;', '>' => '&gt;', '"' => '&quot;']);
    }
}
