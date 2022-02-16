<?php

/*
 * This file is part of the Docs Builder package.
 * (c) Ryan Weaver <ryan@symfonycasts.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SymfonyDocsBuilder\Directive;

use Doctrine\RST\Directives\SubDirective;
use Doctrine\RST\Nodes\FigureNode;
use Doctrine\RST\Nodes\Node;
use Doctrine\RST\Parser;

class FigureDirective extends SubDirective
{
    public function getName(): string
    {
        return 'figure';
    }

    final public function processSub(Parser $parser, ?Node $document, string $variable, string $data, array $options): ?Node
    {
        $wrapperDiv = $parser->renderTemplate(
            'directives/figure.html.twig',
            [
                'custom_css_classes' => $options['class'] ?? '',
                'alt' => $options['alt'] ?? '',
                'height' => $options['height'] ?? null,
                'width' => $options['width'] ?? null,
            ]
        );

        return $parser->getNodeFactory()->createWrapperNode($document, $wrapperDiv, '</div>');
    }
}
