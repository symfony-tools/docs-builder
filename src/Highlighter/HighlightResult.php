<?php

namespace SymfonyTools\GuidesExtension\Highlighter;

final class HighlightResult
{
    public function __construct(
        public readonly string $language,
        public readonly string $code
    ) {
    }
}
