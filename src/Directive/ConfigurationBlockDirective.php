<?php

namespace SymfonyDocs\Directive;

use Doctrine\RST\Document;
use Doctrine\RST\Nodes\CodeNode;
use Doctrine\RST\Nodes\Node;
use Doctrine\RST\Nodes\RawNode;
use Doctrine\RST\Parser;
use Doctrine\RST\SubDirective;
use function strtoupper;

class ConfigurationBlockDirective extends SubDirective
{
    public function getName() : string
    {
        return 'configuration-block';
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
    ) : ?Node {
        if (! $document instanceof Document) {
            return null;
        }

        $html = '<div class="configuration-block jsactive clearfix"><ul class="simple">';

        foreach ($document->getNodes() as $node) {
            if (! $node instanceof CodeNode) {
                continue;
            }

            $language = $node->getLanguage() ?? 'Unknown';

            $html .= '<li>';
            $html .= '<em>' . strtoupper($language) . '</em>';
            $html .= '<div class="highlight-' . $language . '">' . $node->render() . '</div>';
            $html .= '</li>';
        }

        $html .= '</ul>';
        $html .= '</div>';

        return new RawNode($html);
    }
}
