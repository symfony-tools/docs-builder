<?php

namespace SymfonyDocsBuilder\TextRole;

use Doctrine\RST\Environment;
use Doctrine\RST\Span\SpanToken;
use Doctrine\RST\TextRoles\BaseTextRole;

abstract class ExternalLinkRole extends BaseTextRole
{
    protected $baseUrl;

    public function __construct(string $baseUrl)
    {
        $this->baseUrl = $baseUrl;
    }

    protected function renderLink(Environment $environment, string $text, string $url, ?string $title = null)
    {
        return $this->renderTemplate($environment, 'textroles/link', [
            'title' => $text,
            'url' => $url,
            'attributes' => [
                'title' => $title ?? $text,
            ],
        ]);
    }
}
