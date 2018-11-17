<?php

declare(strict_types=1);

namespace SymfonyDocs\Renderers;

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

        if (!$attributes) {
            $attributes['class'] = sprintf('reference %s', 0 === strpos($url, 'http') ? 'external' : 'internal');
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
}
