<?php

namespace SymfonyDocsBuilder\Code\Extractor;

class TerminalExtractor
{
    /**
     * @return string[]
     */
    public function getCommands(string $terminalCode): array
    {
        $lines = explode("\n", trim($terminalCode));
        $commands = [];

        foreach ($lines as $line) {
            if ($line === '') {
                continue;
            }

            switch (substr($line, 0, 2)) {
                case '$ ':
                    $commands[] = substr($line, 2);
                    break;
                case '# ':
                    break;
                default:
                    throw new \Exception(sprintf('Invalid terminal line format "%s" from full terminal block "%s"', $line, $terminalCode));
            }
        }

        return $commands;
    }
}