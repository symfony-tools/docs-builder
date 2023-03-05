<?php

namespace SymfonyDocsBuilder\TextRole;

use Doctrine\RST\Environment;
use Doctrine\RST\Span\SpanToken;

class PhpMethodRole extends ExternalLinkRole
{
    public function getName(): string
    {
        return 'phpmethod';
    }

    protected function buildTitle(SpanToken $spanToken): string
    {
        return parent::buildTitle($spanToken).'()';
    }

    protected function buildUrl(string $data): string
    {
    }

    public function render(Environment $environment, SpanToken $spanToken): string
    {
        $data = $spanToken->get('text');
        [$class, $method] = explode('::', $data, 2);

        return $this->renderLink($environment, $data.'()', sprintf('%s/%s.%s.php', $this->baseUrl, strtolower($class), strtolower($method)));
    }
}
