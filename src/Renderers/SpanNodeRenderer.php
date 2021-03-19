<?php

declare(strict_types=1);

/*
 * This file is part of the Docs Builder package.
 * (c) Ryan Weaver <ryan@symfonycasts.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SymfonyDocsBuilder\Renderers;

use Doctrine\RST\Environment;
use Doctrine\RST\HTML\Renderers\SpanNodeRenderer as BaseSpanNodeRenderer;
use Doctrine\RST\Nodes\SpanNode;
use Doctrine\RST\Templates\TemplateRenderer;
use SymfonyDocsBuilder\CI\UrlChecker;
use function Symfony\Component\String\u;

class SpanNodeRenderer extends BaseSpanNodeRenderer
{
    /** @var TemplateRenderer */
    private $templateRenderer;
    /** @var UrlChecker|null */
    private $urlChecker;

    public function __construct(
        Environment $environment,
        SpanNode $span,
        TemplateRenderer $templateRenderer,
        ?UrlChecker $urlChecker = null
    ) {
        parent::__construct($environment, $span, $templateRenderer);

        $this->templateRenderer = $templateRenderer;
        $this->urlChecker = $urlChecker;
    }

    /**
     * @param mixed[] $attributes
     */
    public function link(?string $url, string $title, array $attributes = []): string
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
            $attributes['rel'] = 'external noopener noreferrer';
            $attributes['target'] = '_blank';
        }

        return $this->templateRenderer->render(
            'link.html.twig',
            [
                'url' => $this->environment->generateUrl($url),
                'title' => $title,
                'attributes' => $attributes,
            ]
        );
    }

    public function literal(string $text): string
    {
        // some browsers can't break long <code> properly, so we inject a
        // `<wbr>` (word-break HTML tag) after some characters to help break those
        // We only do this for very long <code> (4 or more \\) to not break short
        // and common `<code>` such as App\Entity\Something
        if (substr_count($text, '\\') >= 4) {
            // breaking before the backslask is what Firefox browser does
            $text = str_replace('\\', '<wbr>\\', $text);
        }

        return $this->templateRenderer->render('literal.html.twig', ['text' => $text]);
    }

    private function isExternalUrl($url): bool
    {
        return u($url)->containsAny('://');
    }

    /*
     * If the URL is considered safe, it's opened in the same browser tab;
     * otherwise it's opened in a new tab and with some strict security options.
     */
    private function isSafeUrl(string $url): bool
    {
        // The following are considered Symfony URLs:
        //   * https://symfony.com/[...]
        //   * https://[...].symfony.com/ (e.g. insight.symfony.com, etc.)
        //   * https://symfony.wip/[...]  (used for internal/local development)
        $isSymfonyUrl = preg_match('{^http(s)?://(.*\.)?symfony.(com|wip)}', $url);
        $isRelativeUrl = !str_starts_with($url, 'http://') && !str_starts_with($url, 'https://');

        return $isSymfonyUrl || $isRelativeUrl;
    }
}
