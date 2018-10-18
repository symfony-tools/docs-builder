<?php

namespace SymfonyDocs\Nodes;

use Doctrine\RST\HTML\Span;

class SpanNode extends Span
{
    public function literal(string $text): string
    {
        return sprintf('<code class="notranslate">%s</code>', $text);
    }

    public function link(?string $url, string $title, array $attributes = []): string
    {
        if (isset($attributes['is-ref'])) {
            $title = '<span>'.$title.'</span>';
            unset($attributes['is-ref']);
        }
        if (isset($attributes['is-doc'])) {
            $title = '<em>'.$title.'</em>';
            unset($attributes['is-doc']);
        }

        if (!$attributes) {
            $attributes['class'] = sprintf('reference %s', 0 === strpos($url, 'http') ? 'external' : 'internal');
        }

        $htmlAttributes = implode(
            ' ',
            array_map(
                static function ($attribute, $value) {
                    return sprintf('%s="%s"', $attribute, htmlspecialchars((string) $value));
                },
                array_keys($attributes),
                $attributes
            )
        );

        if ($htmlAttributes) {
            $htmlAttributes = ' '.$htmlAttributes;
        }

        return sprintf('<a href="%s"%s>%s</a>', htmlspecialchars((string) $url), $htmlAttributes, $title);
    }
}
