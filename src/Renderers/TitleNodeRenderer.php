<?php

declare(strict_types=1);

/*
 * This file is part of the Docs Builder package.
 * (c) Ryan Weaver <ryan@symfonycasts.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SymfonyDocsBuilder\Renderers;

use Doctrine\RST\Environment;
use Doctrine\RST\Nodes\TitleNode;
use Doctrine\RST\Renderers\NodeRenderer;
use Doctrine\RST\Templates\TemplateRenderer;

class TitleNodeRenderer implements NodeRenderer
{
    /** @var TitleNode */
    private $titleNode;

    /** @var TemplateRenderer */
    private $templateRenderer;

    private static $idUsagesCountByFilename = [];

    public function __construct(TitleNode $titleNode, TemplateRenderer $templateRenderer)
    {
        $this->titleNode = $titleNode;
        $this->templateRenderer = $templateRenderer;
    }

    public static function resetHeaderIdCache(): void
    {
        self::$idUsagesCountByFilename = [];
    }

    public function render(): string
    {
        $filename = $this->titleNode->getEnvironment()->getCurrentFileName();
        $id = $this->titleNode->getId();

        $idUsagesCount = self::$idUsagesCountByFilename[$filename][$id] ?? 0;

        if (0 === $idUsagesCount) {
            $computedId = $this->titleNode->getId();
        } else {
            $computedId = Environment::slugify($this->titleNode->getValue()->getText().'-'.$idUsagesCount);
        }

        self::$idUsagesCountByFilename[$filename][$id] = $idUsagesCount + 1;

        return $this->templateRenderer->render('header-title.html.twig', [
            'titleNode' => $this->titleNode,
            'id' => $computedId,
        ]);
    }
}
