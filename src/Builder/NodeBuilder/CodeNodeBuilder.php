<?php

namespace SymfonyDocs\Builder\NodeBuilder;

use Doctrine\RST\Nodes\CodeNode;
use SymfonyDocs\Builder\BuildableDocument;

class CodeNodeBuilder
{
    public function build(CodeNode $node, BuildableDocument $doc)
    {
        /** @var CodeNode $node */
        if ($node->getLanguage() === 'terminal') {
            // TODO
            // run command if needed

            $lines = $this->extractCommands($node->getValue());

            var_dump('RUN:'. $node->getValue());

            return;
        }

        // TODO - handle other languages
    }

    private function extractCommands(string $terminalCode)
    {
        $lines = explode("\n", $terminalCode);
        $commandLines = [];

        foreach ($lines as $line) {
            switch (substr($line, 0, 2)) {
                case '$ ':

                    break;
                case '# ':
                    break;
                default:
                    throw new \Exception(sprintf('Invalid terminal line format "%s" from full terminal block "%s"', $line, $terminalCode));
            }
        }
    }
}