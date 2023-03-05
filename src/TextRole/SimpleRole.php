<?php

namespace SymfonyDocsBuilder\TextRole;

use Doctrine\RST\Environment;
use Doctrine\RST\Span\SpanToken;
use Doctrine\RST\TextRoles\BaseTextRole;

/**
 * Returns the text inside the role as-is.
 */
class SimpleRole extends BaseTextRole
{
    private $name;

    public function __construct(string $name)
    {
        $this->name = $name;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function render(Environment $environment, SpanToken $spanToken): string
    {
        return $spanToken->get('text');
    }
}
