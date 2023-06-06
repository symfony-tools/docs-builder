<?php

/*
 * This file is part of the Docs Builder package.
 * (c) Ryan Weaver <ryan@symfonycasts.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SymfonyDocsBuilder\Directive;

use Doctrine\RST\Directives\SubDirective;
use Doctrine\RST\Nodes\Node;
use Doctrine\RST\Parser;
use SymfonyDocsBuilder\Node\TabNode;

/**
 * Directive that only appears within the "tabs" directive.
 */
class TabDirective extends SubDirective
{
    public function getName(): string
    {
        return 'tab';
    }

    public function processSub(Parser $parser, ?Node $document, string $variable, string $data, array $options): ?Node
    {
        $tabName = $data;
        if (!$tabName) {
            throw new \RuntimeException(sprintf('The "tab" directive requires a tab name: ".. tab:: Tab Name".'));
        }

        return new TabNode($document->getNodes(), $data);
    }
}
