<?php

namespace SymfonyDocs\CodeBlock;

use Highlight\Highlighter;
use function count;
use function implode;
use function in_array;
use function preg_replace;
use function sha1;
use function sprintf;
use function str_replace;

class CodeBlockWithLineNumbersRenderer
{
    /**
     * These languages exist in our docs but can't be highlighted by the library we're using.
     */
    private const LANGUAGES_NOT_TO_HIGHLIGHT = [
        'text',
        'mysql',
        'postgres',
        'html+php',
    ];

    private const LINE_NUMBER_TABLE_COLUMN_TEMPLATE = '<td class="line-number noselect"><a name="%s" class="line-number-anchor" /><a href="%s">%d</a></td>';

    private const CODE_LINE_TABLE_COLUMN_TEMPLATE = '<td class="code-line" rowspan="%d">{{ RENDERED_CODE }}</td>';

    private const CODE_BLOCK_TABLE_TEMPLATE = <<<TEMPLATE
<pre class="code-block-table">
    <code class="%s">
        <button
            type="button"
            class="copy-to-clipboard"
            data-copy-element-id="%s"
            title="Copy to Clipboard"
        >
            <i class="fas fa-copy"></i>
        </button>
        <div id="%s">%s</div>
    </code>
</pre>
TEMPLATE;

    /** @var Highlighter */
    private $highlighter;

    public function __construct(Highlighter $highlighter)
    {
        $this->highlighter = $highlighter;
    }

    /**
     * @param string[] $lines
     */
    public function render(array $lines, string $language) : string
    {
        $renderedCode = $this->renderCode($lines, $language);

        $codeElementId = sha1($renderedCode);

        $lineNumbersTable = str_replace(
            '{{ RENDERED_CODE }}',
            $renderedCode,
            $this->generateLineNumbersTableTemplate($lines, $codeElementId)
        );

        // trim new lines and white space from html
        $template = preg_replace('~>\s+<~', '><', self::CODE_BLOCK_TABLE_TEMPLATE);

        return sprintf(
            $template,
            $language,
            $codeElementId,
            $codeElementId,
            $lineNumbersTable
        );
    }

    /**
     * @param string[] $lines
     */
    private function renderCode(array $lines, string $language) : string
    {
        $codeToRender = implode("\n", $lines);

        if (! $this->shouldHighlight($language)) {
            return $codeToRender;
        }

        return $this->highlighter->highlight($language, $codeToRender)->value;
    }

    private function shouldHighlight(string $language) : bool
    {
        return $language !== '' && ! in_array($language, self::LANGUAGES_NOT_TO_HIGHLIGHT, true);
    }

    /**
     * @param string[] $lines
     */
    private function generateLineNumbersTableTemplate(array $lines, string $codeElementId) : string
    {
        $lineTableRows = [];

        foreach ($lines as $key => $line) {
            $lineNumber = $key + 1;

            $anchor = sprintf('line-number-%s-%d', $codeElementId, $lineNumber);
            $link   = sprintf('#%s', $anchor);

            $lineTableRow  = '<tr>';
            $lineTableRow .= sprintf(
                self::LINE_NUMBER_TABLE_COLUMN_TEMPLATE,
                $anchor,
                $link,
                $lineNumber
            );

            if ($lineNumber === 1) {
                $lineTableRow .= sprintf(self::CODE_LINE_TABLE_COLUMN_TEMPLATE, count($lines));
            }

            $lineTableRow .= '</tr>';

            $lineTableRows[] = $lineTableRow;
        }

        $lineNumbersTable  = '<table class="code-block-table">';
        $lineNumbersTable .= implode("\n", $lineTableRows);
        $lineNumbersTable .= '</table>';

        return $lineNumbersTable;
    }
}
