<?php

/*
 * This file is part of the Docs Builder package.
 * (c) Ryan Weaver <ryan@symfonycasts.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SymfonyDocsBuilder\Directive;

use Doctrine\RST\Directives\Directive;
use Doctrine\RST\Nodes\Node;
use Doctrine\RST\Parser;

class FigureDirective extends Directive
{
    public function getName(): string
    {
        return 'figure';
    }

    final public function processSub(Parser $parser, ?Node $document, string $variable, string $data, array $options): ?Node
    {
die('here');exit;
        $wrapperDiv = $parser->renderTemplate(
            'directives/figure.html.twig',
            [
                'custom_css_classes' => $options['class'] ?? '',
            ]
        );

        return $parser->getNodeFactory()->createWrapperNode($document, $wrapperDiv, '</div>');
    }
}
