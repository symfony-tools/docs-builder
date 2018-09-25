<?php

namespace SymfonyDocs\Directive;

use Doctrine\RST\Directive;
use Doctrine\RST\Nodes\CodeNode;
use Doctrine\RST\Nodes\Node;
use Doctrine\RST\Nodes\RawNode;
use Doctrine\RST\Parser;
use SymfonyDocs\CodeBlock\CodeBlockRenderer;
use function array_reverse;
use function assert;
use function is_array;
use function is_string;
use function preg_split;
use function trim;

/**
 * Renders a code block, example:
 *
 * .. code-block:: php
 *
 *      <?php
 *
 *      echo "Hello world!\n";
 */
class CodeBlockDirective extends Directive
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

    public function getName(): string
    {
        return 'code-block';
    }

    /**
     * @param string[] $options
     */
    public function process(
        Parser $parser,
        ?Node $node,
        string $variable,
        string $data,
        array $options
    ): void {
        if (!$node instanceof CodeNode) {
            return;
        }

        $nodeValue = $node->getValue();
        assert(is_string($nodeValue));
        $lines = $this->getLines($nodeValue);
        $node->setLanguage($data);

        $lineNumbers = "";
        for ($i = 1; $i <= \count($lines); $i++) {
            $iAsString = (string) $i;
            if (strlen($iAsString) == 1) {
                $iAsString = " ".$iAsString;
            }
            $lineNumbers .= $iAsString ."\n";
        }

        $nodeValue = sprintf(
            self::CODE_BLOCK_TEMPLATE,
            $data,
            Rtrim($lineNumbers),
            implode("\n", $lines)
        );

        $node->setValue($nodeValue);

        $node->setRaw(true);

        if ($variable !== '') {
            $environment = $parser->getEnvironment();
            $environment->setVariable($variable, $node);
        } else {
            $document = $parser->getDocument();
            $document->addNode($node);
        }
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

    public function wantCode(): bool
    {
        return true;
    }
}
