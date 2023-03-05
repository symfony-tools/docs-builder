<?php

namespace SymfonyDocsBuilder\TextRole;

use Doctrine\RST\Environment;
use Doctrine\RST\Span\SpanToken;
use Doctrine\RST\TextRoles\LiteralTextRole;

class LiteralRole extends LiteralTextRole
{
    public function render(Environment $environment, SpanToken $spanToken): string
    {
        $token = clone $spanToken;
        $text = $token->get('text');

        // some browsers can't break long <code> properly, so we inject a
        // `<wbr>` (word-break HTML tag) after some characters to help break those
        // We only do this for very long <code> (4 or more \\) to not break short
        // and common `<code>` such as App\Entity\Something
        if (substr_count($text, '\\') >= 4) {
            // breaking before the backslask is what Firefox browser does
            $text = str_replace('\\', '<wbr>\\', $text);
        }

        $token->set('text', $text);

        return parent::render($environment, $token);
    }
}
