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
use Doctrine\RST\References\ResolvedReference;
use Doctrine\RST\Renderers\SpanNodeRenderer as AbstractSpanNodeRenderer;
use SymfonyDocsBuilder\CI\UrlChecker;
use function Symfony\Component\String\u;

class SpanNodeRenderer extends AbstractSpanNodeRenderer
{
    /** @var BaseSpanNodeRenderer */
    private $decoratedSpanNodeRenderer;
    /** @var UrlChecker|null */
    private $urlChecker;
    private $symfonyVersion;

    public function __construct(
        Environment $environment,
        SpanNode $span,
        BaseSpanNodeRenderer $decoratedSpanNodeRenderer,
        ?UrlChecker $urlChecker = null,
        string $symfonyVersion = null
    )
    {
        parent::__construct($environment, $span);

        $this->decoratedSpanNodeRenderer = $decoratedSpanNodeRenderer;
        $this->urlChecker = $urlChecker;
        $this->symfonyVersion = $symfonyVersion;
    }

    public function render(): string
    {
        // Work around "~" being parsed as non-breaking space by rst-parser,
        // while this is not part of the specification.
        $spanValue = $this->span->getValue();
        $spanValue = str_replace('~', '__TILDE__', $spanValue);
        $this->span->setValue($spanValue);

        $rendered = parent::render();

        return str_replace('__TILDE__', '~', $rendered);
    }

    /** @inheritDoc */
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
            $attributes = $this->addAttributesForUnsafeUrl($attributes);
        }

        if (null !== $this->symfonyVersion) {
            $url = u($url)->replace('{version}', $this->symfonyVersion)->toString();
        }

        return $this->decoratedSpanNodeRenderer->link($url, $title, $attributes);
    }

    public function reference(ResolvedReference $reference, array $value): string
    {
        if (!$this->isSafeUrl($reference->getUrl())) {
            $reference = new ResolvedReference(
                $reference->getFile(),
                $reference->getTitle(),
                $reference->getUrl(),
                $reference->getTitles(),
                $this->addAttributesForUnsafeUrl($reference->getAttributes())
            );
        }

        return $this->decoratedSpanNodeRenderer->reference($reference, $value);
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

        return $this->decoratedSpanNodeRenderer->literal($text);
    }

    public function emphasis(string $text): string
    {
        return $this->decoratedSpanNodeRenderer->emphasis($text);
    }

    public function strongEmphasis(string $text): string
    {
        return $this->decoratedSpanNodeRenderer->strongEmphasis($text);
    }

    public function nbsp(): string
    {
        return $this->decoratedSpanNodeRenderer->nbsp();
    }

    public function br(): string
    {
        return $this->decoratedSpanNodeRenderer->br();
    }

    public function escape(string $span): string
    {
        return $this->decoratedSpanNodeRenderer->escape($span);
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

    private function addAttributesForUnsafeUrl(array $attributes): array
    {
        return array_merge(
            $attributes,
            ['rel' => 'external noopener noreferrer', 'target' => '_blank']
        );
    }
}
