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

class CodeNodeRenderer implements NodeRenderer
{
    private static $isHighlighterConfigured = false;

    private const LANGUAGES_MAPPING = [
        'caddy' => 'plaintext',
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
        $highlightingLanguage = self::LANGUAGES_MAPPING[$language] ?? $language;
        $languages = array_unique([$language, $highlightingLanguage]);

        if ('text' === $language) {
            // Highlighter escapes correctly the code, we need to manually escape only for "text" code
            $highlightedCode = $this->escapeForbiddenCharactersInsideCodeBlock($code);
        } else {
            $this->configureHighlighter();

            $highLighter = new Highlighter();
            $highlightedCode = $highLighter->highlight($highlightingLanguage, $code)->value;
        }

        if ('php' === $highlightingLanguage) {
            $highlightedCode = $this->processHighlightedPhpCode($highlightedCode);
        }

        if ('terminal' === $language) {
            $highlightedCode = preg_replace('/^\$ /m', '<span class="hljs-prompt">$ </span>', $highlightedCode);
            $highlightedCode = preg_replace('/^C:\\\&gt; /m', '<span class="hljs-prompt">C:\&gt; </span>', $highlightedCode);
        }

        $numOfLines = \count(preg_split('/\r\n|\r|\n/', $highlightedCode));
        $lineNumbers = implode("\n", range(1, $numOfLines));

        // 'caption' is used by code blocks to define the path of the file they belong to
        // 'patch_file' is a special value used by "diff patches", which don't correspond to any file
        $codeCaption = $this->codeNode->getOptions()['caption'] ?? null;
        if ('patch_file' === $codeCaption) {
            $codeCaption = null;
        }

        return $this->templateRenderer->render(
            'code.html.twig',
            [
                'custom_css_classes' => $this->codeNode->getClassesString(),
                'languages' => $languages,
                'line_numbers' => $lineNumbers,
                'code' => $highlightedCode,
                'loc' => $numOfLines,
                // the length of the codeblock in a semantic way (to tweak styling)
                // e.g. LOC = 5, length = 'sm'; LOC = 18, length = 'md'
                'length' => [1 => 'sm', 2 => 'md', 3 => 'lg', 4 => 'xl'][strlen((string) $numOfLines)],
                'caption' => $codeCaption,
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

    private function configureHighlighter()
    {
        if (false === self::$isHighlighterConfigured) {
            Highlighter::registerLanguage('php', __DIR__.'/../Templates/highlight.php/php.json', true);
            Highlighter::registerLanguage('twig', __DIR__.'/../Templates/highlight.php/twig.json', true);
        }

        self::$isHighlighterConfigured = true;
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

    private function processHighlightedPhpCode(string $highlightedCode): string
    {
        // this allows to highlight the $ in PHP variable names
        $highlightedCode = str_replace('<span class="hljs-variable">$', '<span class="hljs-variable"><span class="hljs-variable-other-marker">$</span>', $highlightedCode);

        if (!str_contains($highlightedCode, '<span class="hljs-comment">#[')) {
            return $highlightedCode;
        }

        // this highlights PHP attributes, which can be defined in many different ways:
        //
        //   #[AttributeName]
        //   #[AttributeName()]
        //   #[AttributeName('value')]
        //   #[AttributeName('value', option: 'value')]
        //   #[AttributeName(['value' => 'value'])]
        //   #[AttributeName(
        //       'value',
        //       option: 'value'
        //   )]
        //
        // The attribute name is mandatory, but the parentheses and the arguments are optional.
        $highlightedCode = preg_replace_callback(
            '/<span class="hljs-comment">#\[\s*(?<name>[a-zA-Z_\\\\][\w\\\\]*)(?<arguments>\(.*\))?\s*\]/Us',
            static function (array $matches) {
                $attributeName = $matches['name'];
                $attributeArguments = $matches['arguments'] ?? '';

                if ('' === $attributeArguments) {
                    return sprintf('<span class="hljs-php-attribute">#[%s]</span>', $attributeName);
                }

                // the tricky part is to highlight the values and options; so we
                // use the highlighter to highlight the whole attribute wrapped with
                // some contents to make it valid PHP code
                // Original string to highlight: AttributeName('value', option: 'value')
                // String passed to highlighter: $attribute = new AttributeName('value', option: 'value');
                // After highlighting, we remove the `$attribute = new ` prefix and the trailing `;`
                $highlighter = new Highlighter();

                // this is needed because when using 'class' as the name of an attribute argument, the highlighter
                // confuses it for a new class instantiation and highlights it as such
                $attributeArguments = str_replace('class:', 'klass:', $attributeArguments);

                // this happens in multiline attributes, where the highlighter already highlighted each line of the attribute (except the attribute name)
                if (str_contains($attributeArguments, '<span class="hljs-string') || str_contains($attributeArguments, '<span class="hljs-number')) {
                    // don't trim the result to keep the leading and trailing \n
                    $highlightedAttribute = preg_replace('/\(<\/span>(.*)\)$/s', '$1', $attributeArguments);
                } else {
                    $highlightedAttribute = $highlighter->highlight('php', sprintf('$hljsAttribute = new %s%s;', $attributeName, $attributeArguments))->value;
                    $highlightedAttribute = preg_replace('/^<span class="hljs-variable">\$hljsAttribute<\/span> = <span class="hljs-keyword">new<\/span> (.*);$/', '$1', $highlightedAttribute);

                    // fix the double transformation of < to &amp;&lt; and > to &amp;&gt; caused by the highlighter
                    $highlightedAttribute = str_replace('&amp;lt;', '&lt;', $highlightedAttribute);
                    $highlightedAttribute = str_replace('&amp;gt;', '&gt;', $highlightedAttribute);

                    // $highlightedAttribute is like 'Route(<span class="hljs-string">'/posts/{id}'</span>)'
                    // remove the attribute name and the parenthesis from the highlighted code
                    $highlightedAttribute = substr(
                        $highlightedAttribute,
                        strlen($attributeName) + 1,
                        -1
                    );
                }

                // reverse the previous change needed to avoid highlighting 'class' as a new class instantiation
                $highlightedAttribute = str_replace('klass:', 'class:', $highlightedAttribute);

                return sprintf('<span class="hljs-php-attribute">#[%s(</span>%s<span class="hljs-php-attribute">)]</span>', $attributeName, $highlightedAttribute);
            },
            $highlightedCode
        );

        return $highlightedCode;
    }
}
