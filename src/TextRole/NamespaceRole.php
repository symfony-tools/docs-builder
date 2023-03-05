<?php

namespace SymfonyDocsBuilder\TextRole;

use Doctrine\RST\Environment;
use Doctrine\RST\Span\SpanToken;
use function Symfony\Component\String\u;

class NamespaceRole extends ExternalLinkRole
{
    public function getName(): string
    {
        return 'namespace';
    }

    public function render(Environment $environment, SpanToken $spanToken): string
    {
        $data = $spanToken->get('text');
        $namespaceName = u($data)->replace('\\\\', '\\');

        return $this->renderLink(
            $environment,
            $namespaceName->afterLast('\\'),
            sprintf('%s/%s', $this->baseUrl, $namespaceName->replace('\\', '/')),
            $namespaceName
        );
    }
}
