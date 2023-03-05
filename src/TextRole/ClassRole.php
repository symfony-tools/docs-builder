<?php

namespace SymfonyDocsBuilder\TextRole;

use Doctrine\RST\Environment;
use Doctrine\RST\Span\SpanToken;
use function Symfony\Component\String\u;

class ClassRole extends ExternalLinkRole
{
    public function getName(): string
    {
        return 'class';
    }

    public function render(Environment $environment, SpanToken $spanToken): string
    {
        $data = $spanToken->get('text');
        $className = u($data)->replace('\\\\', '\\');

        return $this->renderLink(
            $environment,
            $className->afterLast('\\'),
            sprintf('%s/%s.php', $this->baseUrl, $className->replace('\\', '/')),
            $className
        );
    }
}
