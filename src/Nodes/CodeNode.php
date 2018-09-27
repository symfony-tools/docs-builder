<?php

namespace SymfonyDocs\Nodes;

use Doctrine\RST\Nodes\CodeNode as Base;
use function htmlspecialchars;

class CodeNode extends Base
{
    private const CODE_BLOCK_TEMPLATE = '<div class="literal-block notranslate">
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
                        <pre>%s</pre>
                    </div>
                </td>
            </tr>
        </table>
    </div>
</div>';

    public function render() : string
    {
        $nodeValue = $this->getValue();
        assert(is_string($nodeValue));
        $lines = $this->getLines($nodeValue);

        $lineNumbers = "";
        for ($i = 1; $i <= \count($lines); $i++) {
            $iAsString = (string) $i;
            if (strlen($iAsString) == 1) {
                $iAsString = " ".$iAsString;
            }
            $lineNumbers .= $iAsString."\n";
        }

        return sprintf(
            self::CODE_BLOCK_TEMPLATE,
            $this->getLanguage() ?? 'php',
            rtrim($lineNumbers),
            implode("\n", $lines)
        );
    }

    /**
     * @return string[]
     */
    private function getLines(string $code): array
    {
        $lines = preg_split('/\r\n|\r|\n/', $code);
        assert(is_array($lines));

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
