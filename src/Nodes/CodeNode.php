<?php

namespace SymfonyDocs\Nodes;

use Doctrine\RST\Nodes\CodeNode as Base;
use Highlight\Highlighter;

class CodeNode extends Base
{
    private const LANGUAGES_MAPPING = [
        'php'             => 'php',
        'html'            => 'html',
        'xml'             => 'xml',
        'yaml'            => 'yaml',
        'twig'            => 'twig',
        'ini'             => 'ini',
        'bash'            => 'bash',
        'html+twig'       => 'twig',
        'jinja'           => 'twig',
        'html+php'        => 'html',
        'php-annotations' => 'php',
        'text'            => 'text',
        'terminal'        => 'bash',
        'markdown'        => 'markdown',
        'rst'             => 'markdown',
        'php-standalone'  => 'php',
        'php-symfony'     => 'php',
        'varnish4'        => 'c',
        'varnish3'        => 'c',
        'json'            => 'json',
    ];

    private const CODE_BLOCK_TEMPLATE = <<< TEMPLATE
<div class="literal-block notranslate">
    <div class="highlight-%s">
        <table class="highlighttable">
            <tr>
                <td class="linenos">
                    <div class="linenodiv">
                        <pre>%s</pre>
                    </div>
                </td>
                <td class="code">
                    <div class="highlight">
                        <pre class="hljs %s">%s</pre>
                    </div>
                </td>
            </tr>
        </table>
    </div>
</div>
TEMPLATE;

    public function render(): string
    {
        $nodeValue = $this->getValue();
        assert(is_string($nodeValue));
        $lines = $this->getLines($nodeValue);
        $code  = implode("\n", $lines);

        $lineNumbers = "";
        for ($i = 1; $i <= \count($lines); $i++) {
            $lineNumbers .= str_pad((string) $i, 2, ' ', STR_PAD_LEFT)."\n";
        }

        $language = $this->getLanguage() ?? 'php';

        if (!isset(self::LANGUAGES_MAPPING[$language])) {
            throw new \RuntimeException(sprintf('Language "%s" is unknown', $language));
        }

        if ('text' !== $language) {
            $highLighter = new Highlighter();
            $code        = $highLighter->highlight(self::LANGUAGES_MAPPING[$language], $code)->value;
        }

        return sprintf(
            self::CODE_BLOCK_TEMPLATE,
            $language,
            rtrim($lineNumbers),
            self::LANGUAGES_MAPPING[$language],
            $code
        );
    }

    private function getLines(string $code): array
    {
        $lines         = preg_split('/\r\n|\r|\n/', $code);
        $reversedLines = array_reverse($lines);

        // trim empty lines at the end of the code
        foreach ($reversedLines as $key => $line) {
            if (trim($line) !== '') {
                break;
            }

            unset($reversedLines[$key]);
        }

        return array_reverse($reversedLines);
    }
}
