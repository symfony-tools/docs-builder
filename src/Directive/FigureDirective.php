<?php

/*
 * This file is part of the Docs Builder package.
 * (c) Ryan Weaver <ryan@symfonycasts.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SymfonyDocsBuilder\Directive;

use Doctrine\RST\Directives\Directive;
use Doctrine\RST\Nodes\FigureNode;
use Doctrine\RST\Nodes\Node;
use Doctrine\RST\Parser;

class FigureDirective extends Directive
{
    public function getName(): string
    {
        return 'figure';
    }

    public function process(Parser $parser, ?Node $node, string $variable, string $data, array $options): void
    {
        if (!$node instanceof FigureNode) {
            return;
        }

        // grab the "class" option and forward it onto the Node
        // FigureRenderer can then use it when rendering
        $node->setClasses(isset($options['class']) ? explode(' ', $options['figclass']) : []);
    }
}
