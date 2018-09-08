<?php

namespace SymfonyDocs\CodeBlock;

use function in_array;

class CodeBlockRenderer
{
    private const CONSOLE_LANGUAGES = ['bash', 'sh', 'console'];

    /** @var CodeBlockConsoleRenderer */
    private $codeBlockConsoleRenderer;

    /** @var CodeBlockWithLineNumbersRenderer */
    private $codeBlockWithLineNumbersRenderer;

    public function __construct(
        CodeBlockConsoleRenderer $codeBlockConsoleRenderer,
        CodeBlockWithLineNumbersRenderer $codeBlockWithLineNumbersRenderer
    ) {
        $this->codeBlockConsoleRenderer         = $codeBlockConsoleRenderer;
        $this->codeBlockWithLineNumbersRenderer = $codeBlockWithLineNumbersRenderer;
    }

    /**
     * @param string[] $lines
     */
    public function render(array $lines, string $language) : string
    {
        if (in_array($language, self::CONSOLE_LANGUAGES, true)) {
            return $this->codeBlockConsoleRenderer->render(
                $lines
            );
        }

        return $this->codeBlockWithLineNumbersRenderer->render(
            $lines,
            $language
        );
    }
}
