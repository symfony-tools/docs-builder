<?php

namespace SymfonyDocs\Directive;

use Doctrine\RST\Directive;
use Doctrine\RST\Nodes\CodeNode;
use Doctrine\RST\Nodes\Node;
use Doctrine\RST\Parser;
use SymfonyDocs\CodeBlock\CodeBlockLanguageDetector;
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
    /** @var CodeBlockRenderer */
    private $codeBlockRenderer;

    /** @var CodeBlockLanguageDetector */
    private $codeBlockLanguageDetector;

    public function __construct(
        CodeBlockRenderer $codeBlockRenderer,
        CodeBlockLanguageDetector $codeBlockLanguageDetector
    ) {
        $this->codeBlockRenderer         = $codeBlockRenderer;
        $this->codeBlockLanguageDetector = $codeBlockLanguageDetector;
    }

    public function getName() : string
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
    ) : void {
        if (! $node instanceof CodeNode) {
            return;
        }

        $kernel = $parser->getKernel();

        $nodeValue = $node->getValue();
        assert(is_string($nodeValue));

        $lines = $this->getLines($nodeValue);

        $language = $this->codeBlockLanguageDetector->detectLanguage($data, $lines);

        $node->setLanguage($language);

        $codeBlock = $this->codeBlockRenderer->render($lines, $language);

        $node->setRaw(true);
        $node->setValue($codeBlock);

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
    private function getLines(string $code) : array
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

    public function wantCode() : bool
    {
        return true;
    }
}
