<?php

namespace SymfonyDocsBuilder\TextRole;

use Doctrine\RST\Environment;
use Doctrine\RST\Span\SpanToken;

class PhpClassRole extends ExternalLinkRole
{
    public function getName(): string
    {
        return 'phpclass';
    }

    public function render(Environment $environment, SpanToken $spanToken): string
    {
        $data = $spanToken->get('text');

        return $this->renderLink($environment, $data, sprintf('%s/class.%s.php', $this->baseUrl, strtolower($data)));
    }
}
