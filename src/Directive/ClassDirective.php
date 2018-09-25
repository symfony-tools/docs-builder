<?php

namespace SymfonyDocs\Directive;

use Doctrine\RST\Nodes\Node;
use Doctrine\RST\Nodes\RawNode;
use Doctrine\RST\Parser;
use Doctrine\RST\SubDirective;

class ClassDirective extends SubDirective
{
    public function getName() : string
    {
        return 'class';
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
        $dOMDocument = new \DOMDocument();
        $dOMDocument->loadHTML((string) $document, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);

        /** @var \DOMElement $firstNode */
        $firstNode = $dOMDocument->childNodes[0];
        $firstNodeClass = $firstNode->getAttribute('class');
        $firstNode->setAttribute('class', trim(sprintf('%s %s', $firstNodeClass, $data)));

        return new RawNode($dOMDocument->saveHTML());
    }


}
