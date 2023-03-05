<?php

namespace SymfonyDocsBuilder\TextRole;

use Doctrine\RST\Environment;
use Doctrine\RST\TextRoles\LinkTextRole;
use SymfonyDocsBuilder\CI\UrlChecker;
use function Symfony\Component\String\u;

class LinkRole extends LinkTextRole
{
    /** @var UrlChecker|null */
    private $urlChecker;

    public function __construct(?UrlChecker $urlChecker = null)
    {
        parent::__construct();

        $this->urlChecker = $urlChecker;
    }

    public function renderLink(Environment $environment, ?string $url, string $title, array $attributes = []): string
    {
        $url = (string) $url;

        if (
            $this->urlChecker &&
            $this->isExternalUrl($url) &&
            !u($url)->startsWith(['http://localhost', 'http://192.168'])
        ) {
            $this->urlChecker->checkUrl($url);
        }

        if (!$this->isSafeUrl($url)) {
            $attributes = $this->addAttributesForUnsafeUrl($attributes);
        } else {
            $attributes['class'] = 'reference internal';
        }

        return $environment->getTemplateRenderer()->render('textroles/link.html.twig', [
            'url' => $environment->generateUrl((string) $url),
            'title' => $title,
            'attributes' => $attributes,
        ]);
    }

    private function addAttributesForUnsafeUrl(array $attributes): array
    {
        return array_merge(
            $attributes,
            ['class' => 'reference external', 'rel' => 'external noopener noreferrer', 'target' => '_blank']
        );
    }
}
