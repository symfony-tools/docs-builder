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

    public function __construct(
        BaseSpanNodeRenderer $decoratedSpanNodeRenderer,
        Environment $environment,
        SpanNode $span,
        ?UrlChecker $urlChecker = null
    ) {
        parent::__construct($environment, $span);

        $this->decoratedSpanNodeRenderer = $decoratedSpanNodeRenderer;
        $this->urlChecker = $urlChecker;
    }

    /** @inheritDoc */
    public function link(?string $url, string $title, array $attributes = []): string
    {
        $url = (string)$url;

        if (
            $this->urlChecker &&
            $this->isExternalUrl($url) &&
            !u($url)->startsWith(['http://localhost', 'http://192.168'])
        ) {
            $this->urlChecker->checkUrl($url);
        }

        return $this->decoratedSpanNodeRenderer->link($url, $title, $attributes);
    }

    private function isExternalUrl($url): bool
    {
        return u($url)->containsAny('://');
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

    public function literal(string $text): string
    {
        return $this->decoratedSpanNodeRenderer->literal($text);
    }

    public function escape(string $span): string
    {
        return $this->decoratedSpanNodeRenderer->escape($span);
    }

    /** @inheritDoc */
    public function reference(ResolvedReference $reference, array $value): string
    {
        return $this->decoratedSpanNodeRenderer->reference($reference, $value);
    }
}
