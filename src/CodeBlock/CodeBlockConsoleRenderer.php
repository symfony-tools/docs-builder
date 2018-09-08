<?php

namespace SymfonyDocs\CodeBlock;

use function count;
use function implode;
use function ltrim;
use function sprintf;
use function substr;

class CodeBlockConsoleRenderer
{
    /**
     * @param string[] $lines
     */
    public function render(array $lines) : string
    {
        $code = '<div class="console">';

        $consoleLines = [];

        foreach ($lines as $line) {
            $hasDollarSign = isset($line[0]) && $line[0] === '$';

            $consoleLines[] = $this->renderConsoleLine($line, $hasDollarSign);
        }

        $consoleLines = implode("\n", $consoleLines);

        $firstLineHasDollarSign = isset($lines[0][0]) && $lines[0][0] === '$';

        // if console example has only 1 line and no $ explicitely existed put a $ there manually.
        if (count($lines) === 1 && ! $firstLineHasDollarSign) {
            $consoleLines = sprintf('<span class="noselect">$ </span>%s', ltrim($consoleLines)) . "\n";
        }

        $code .= sprintf('<pre><code class="console">%s</code></pre>', $consoleLines);

        $code .= '</div>';

        return $code;
    }

    private function renderConsoleLine(string $line, bool $hasDollarSign) : string
    {
        if ($hasDollarSign) {
            $line = substr($line, 1);

            return sprintf('<span class="noselect">$ </span>%s', ltrim($line));
        }

        return $line;
    }
}
