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

class TabsDirective extends SubDirective
{
    public function getName(): string
    {
        return 'tabs';
    }

    public function processSub(Parser $parser, ?Node $document, string $variable, string $data, array $options): ?Node
    {
        $tabsTitle = $data;
        if (!$tabsTitle) {
            throw new \RuntimeException(sprintf('The "tabs" directive requires a title: ".. tabs:: Title".'));
        }

        $blocks = [];
        foreach ($document->getNodes() as $tabNode) {
            if (!$tabNode instanceof TabNode) {
                throw new \RuntimeException(sprintf('Only ".. tab::" content can appear within the "tabs" directive.'));
            }

            $content = '';
            foreach ($tabNode->getNodes() as $node) {
                $content .= $node->render();
            }

            $blocks[] = [
                'hash' => hash('sha1', $tabNode->getTabName()),
                'language_label' => $tabNode->getTabName(),
                'language' => $tabNode->getSluggedTabName(),
                'code' => $content,
            ];
         }

        $wrapperDiv = $parser->renderTemplate(
            'directives/configuration-block.html.twig',
            [
                'blocks' => $blocks,
                'title' => $tabsTitle,
            ]
        );

        return $parser->getNodeFactory()->createWrapperNode(null, $wrapperDiv, '</div>');
    }
}
