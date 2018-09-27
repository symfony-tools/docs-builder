<?php

namespace SymfonyDocs\Directive;

use Doctrine\RST\Directive;
use Doctrine\RST\Nodes\CodeNode;
use Doctrine\RST\Nodes\Node;
use Doctrine\RST\Parser;
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

        $node->setLanguage($data);

        if ($variable !== '') {
            $environment = $parser->getEnvironment();
            $environment->setVariable($variable, $node);
        } else {
            $document = $parser->getDocument();
            $document->addNode($node);
        }
    }

    public function wantCode(): bool
    {
        return true;
    }
}
