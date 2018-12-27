<?php

declare(strict_types=1);

namespace SymfonyDocsBuilder\Renderers;

use Doctrine\RST\Environment;
use Doctrine\RST\HTML\Renderers\SpanNodeRenderer as BaseSpanNodeRenderer;
use Doctrine\RST\Nodes\SpanNode;
use Doctrine\RST\Templates\TemplateRenderer;

class SpanNodeRenderer extends BaseSpanNodeRenderer
{
    /** @var TemplateRenderer */
    private $templateRenderer;

    public function __construct(
        Environment $environment,
        SpanNode $span,
        TemplateRenderer $templateRenderer
    ) {
        parent::__construct($environment, $span, $templateRenderer);

        $this->templateRenderer = $templateRenderer;
    }

    /**
     * @param mixed[] $attributes
     */
    public function link(?string $url, string $title, array $attributes = []): string
    {
        $url = (string) $url;

        if ($this->isExternalUrl($url)) {
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_HEADER, true);
            curl_setopt($ch, CURLOPT_NOBODY, true);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 2);
            curl_exec($ch);
            $httpCode = (int) curl_getinfo($ch,  CURLINFO_RESPONSE_CODE);
            curl_close($ch);

            if ($httpCode < 200 || $httpCode >= 300) {
                dump("$url : $httpCode");
            }
        }

        if (!$attributes) {
            $attributes['class'] = sprintf('reference %s', $this->isExternalUrl($url) ? 'external' : 'internal');
        }

        return $this->templateRenderer->render(
            'link.html.twig',
            [
                'url'        => $this->environment->generateUrl($url),
                'title'      => $title,
                'attributes' => $attributes,
            ]
        );
    }

    private function isExternalUrl($url): bool
    {
        if (0 === strpos($url, 'http://') || 0 === strpos($url, 'https://')) {
            return true;
        }

        return false;
    }
}
