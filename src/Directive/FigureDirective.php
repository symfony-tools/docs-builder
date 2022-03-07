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

/**
 * Overridden to handle "figclass" properly.
 */
class FigureDirective extends SubDirective
{
    public function getName(): string
    {
        return 'figure';
    }

    /**
     * @param string[] $options
     */
    public function processSub(
        Parser $parser,
        ?Node $document,
        string $variable,
        string $data,
        array $options
    ): ?Node {
        $environment = $parser->getEnvironment();

        $url = $environment->relativeUrl($data);

        if ($url === null) {
            throw new \Exception(sprintf('Could not get relative url for %s', $data));
        }

        $nodeFactory = $parser->getNodeFactory();

        $figClass = $options['figclass'] ?? null;
        unset($options['figclass']);

        $figureNode = $parser->getNodeFactory()->createFigureNode(
            $nodeFactory->createImageNode($url, $options),
            $document
        );
        if ($figClass) {
            $figureNode->setClasses(explode(' ', $figClass));
        }

        return $figureNode;
    }
}
