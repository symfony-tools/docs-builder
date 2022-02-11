<?php

declare(strict_types=1);

/*
 * This file is part of the Docs Builder package.
 * (c) Ryan Weaver <ryan@symfonycasts.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SymfonyDocsBuilder\Renderers;

use Doctrine\RST\Nodes\FigureNode;
use Doctrine\RST\Renderers\NodeRenderer;
use Doctrine\RST\Templates\TemplateRenderer;

class FigureNodeRenderer implements NodeRenderer
{
    /** @var FigureNode */
    private $figureNode;

    /** @var TemplateRenderer */
    private $templateRenderer;

    public function __construct(FigureNode $figureNode, TemplateRenderer $templateRenderer)
    {
        $this->figureNode       = $figureNode;
        $this->templateRenderer = $templateRenderer;
    }

    public function render(): string
    {
        var_dump('here');exit;
        return $this->templateRenderer->render('figure.html.twig', [
            'figureNode' => $this->figureNode,
        ]);
    }
}
