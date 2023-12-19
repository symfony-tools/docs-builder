<?php

/*
 * This file is part of the Guides SymfonyExtension package.
 *
 * (c) Wouter de Jong
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SymfonyTools\GuidesExtension\Highlighter;

use phpDocumentor\Guides\Code\Highlighter\Highlighter;
use phpDocumentor\Guides\Code\Highlighter\HighlightResult;

final class SymfonyHighlighter implements Highlighter
{
    public function __construct(
        private Highlighter $highlighter
    ) {
    }

    public function __invoke(string $language, string $code): HighlightResult
    {
        $result = ($this->highlighter)($language, $code);

        $code = $result->code;
        if ('php' === $result->language) {
            // highlight the $ in PHP variable names
            $code = str_replace('<span class="hljs-variable">$', '<span class="hljs-variable"><span class="hljs-variable-other-marker">$</span>', $code);
        }

        if ('terminal' === $language) {
            $code = preg_replace('/^\$ /m', '<span class="hljs-prompt">$ </span>', $code);
            $code = preg_replace('/^C:\\\&gt; /m', '<span class="hljs-prompt">C:\&gt; </span>', $code);
        }

        return new HighlightResult($result->language, $code);
    }
}
