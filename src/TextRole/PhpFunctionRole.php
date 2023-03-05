<?php

namespace SymfonyDocsBuilder\TextRole;

use Doctrine\RST\Environment;
use Doctrine\RST\Span\SpanToken;
use function Symfony\Component\String\u;

class PhpFunctionRole extends ExternalLinkRole
{
    public function getName(): string
    {
        return 'phpfunction';
    }

    public function render(Environment $environment, SpanToken $spanToken): string
    {
        $data = $spanToken->get('text');

        return $this->renderLink($environment, $data.'()', sprintf('%s/function.%s.php', $this->baseUrl, u($data)->replace('_', '-')->lower()));
    }
}
