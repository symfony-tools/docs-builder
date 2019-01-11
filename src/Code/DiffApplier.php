<?php

namespace SymfonyDocsBuilder\Code;

class DiffApplier
{
    use ConsoleLoggerTrait;

    private $newLines = [];
    private $hasClassStarted = false;
    private $currentLineNumber = 0;
    private $shouldDie = false;

    public function applyPhpDiff(string $targetSource, string $diffSource): string
    {
        $diffLines = explode("\n", $diffSource);
        $this->newLines = explode("\n", $targetSource);
        $this->hasClassStarted = false;
        $this->currentLineNumber = 0;

        foreach ($diffLines as $diffLine) {
            if (strpos($diffLine, '- ') !== false) {
                $this->removeLine(substr($diffLine, 2));

                continue;
            }

            if (strpos($diffLine, '+ ') !== false) {
                $this->addLine(substr($diffLine, 2));

                continue;
            }

            // not a real line - skip
            if (strpos($diffLine, '// ...') !== false) {
                continue;
            }

            // it's a normal line - advance to it
            $this->advanceToLine($diffLine);
        }

        return implode("\n", $this->newLines);
    }

    private function removeLine(string $line)
    {
        $this->logDebug(sprintf('Diff Remove: %s', $line));

        while (!$this->isAtEndOfFile()) {
            if ($this->getLine() === $line) {
                unset($this->newLines[$this->currentLineNumber]);
                $this->newLines = array_values($this->newLines);
                $this->currentLineNumber--;

                return;
            }

            $this->currentLineNumber++;
        }

        throw new \Exception(sprintf('Could not find line to remove: "s"', $line));
    }

    private function addLine(string $line)
    {
        $this->logDebug(sprintf('Diff Add: %s', $line));

        if (!$this->hasClassStarted) {
            // before the class? We assume a use statement or class declaration
            if (strpos($line, 'use ') !== 0) {
                // class declaration? Add it
                if ($this->isLineClassDeclaration($line)) {
                    $this->addLinesAtCurrentPosition([$line]);

                    return;
                }

                throw new \Exception(sprintf('Could not figure out how to add new line "%s"', $line));
            }

            $this->advanceToUseStatementPlacement(substr($line, 4));
            $addLineBreak = $this->isLineClassDeclaration($this->getLine(1));
            $this->addLinesAtCurrentPosition($addLineBreak ? [$line, ''] : [$line]);

            return;
        }

        // add wherever the current line is
        $this->addLinesAtCurrentPosition([$line]);
    }

    private function addLinesAtCurrentPosition(array $lines)
    {
        array_splice(
            $this->newLines,
            $this->currentLineNumber + 1,
            0,
            $lines
        );
        $this->currentLineNumber = $this->currentLineNumber + count($lines);
    }

    private function getLine(int $offset = 0): string
    {
        return $this->newLines[$this->currentLineNumber + $offset] ?? null;
    }

    private function isAtEndOfFile(): bool
    {
        return $this->currentLineNumber === count($this->newLines);
    }

    /**
     * Move forward until the current line is where the use statement
     * for this class should be placed.
     */
    private function advanceToUseStatementPlacement(string $className)
    {
        while (!$this->isAtEndOfFile()) {
            if (strpos($this->getLine(), 'use ') === 0) {
                $foundClass = substr($this->getLine(), 4);
                $arr1 = [$className, $foundClass];
                $arr2 = [$foundClass, $className];
                sort($arr2);

                // if the arrays match, then putting the class here
                // would be correct alphabetically
                if ($arr1 == $arr2) {
                    $this->currentLineNumber--;

                    return;
                }

                // otherwise keep going
                $this->currentLineNumber++;
                continue;
            }

            if ($this->isLineClassDeclaration($this->getLine())) {
                $this->currentLineNumber--;

                return;
            }

            $this->currentLineNumber++;
        }
    }

    private function isLineClassDeclaration(string $line)
    {
        return strpos($line, 'class ') === 0;
    }

    private function advanceToLine(string $line)
    {
        while (!$this->isAtEndOfFile()) {
            if ($this->isLineClassDeclaration($this->getLine())) {
                $this->hasClassStarted = true;
            }

            if ($this->getLine() === $line) {
                return;
            }

            $this->currentLineNumber++;
        }

        throw new \Exception(sprintf('Could not find line in code "%s"', $line));
    }
}
