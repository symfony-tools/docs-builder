<?php

namespace SymfonyDocs\Builder;

use Doctrine\RST\Nodes\CodeNode;
use Doctrine\RST\Parser;
use Symfony\Component\Filesystem\Filesystem;
use SymfonyDocs\Builder\NodeBuilder\CodeNodeBuilder;

class DocumentCodeBuilder
{
    private $parser;
    private $codeNodeBuilder;
    private $fs;

    public function __construct(Parser $parser, CodeNodeBuilder $codeNodeBuilder)
    {
        $this->parser = $parser;
        $this->codeNodeBuilder = $codeNodeBuilder;
        $this->fs = new Filesystem();
    }

    public function createBuildableDocument(string $path)
    {
        $path = realpath($path);
        $currentDir = dirname($path);
        $filename = str_replace($currentDir.'/', '', $path);

        // somehow get the raw Nodes for a specific document
        $this->parser->getEnvironment()->setCurrentFileName($filename);
        $this->parser->getEnvironment()->setCurrentDirectory($currentDir);
        $documentNode = $this->parser->parseFile($path);

        // todo - more obvious filename
        $buildPath = __DIR__.'/../../_code/'.md5($path);

        return new BuildableDocument($filename, $currentDir, $buildPath, $documentNode);
    }

    public function buildDocument(BuildableDocument $doc) {
        if (file_exists($doc->getTargetPath())) {
            $this->fs->remove($doc->getTargetPath());
        }

        // TODO - handle pre-requisite document

        $this->fs->mkdir($doc->getTargetPath());

        foreach ($doc->getNodes() as $node) {
            if (!$node instanceof CodeNode) {
                continue;
            }

            $this->codeNodeBuilder->build($node, $doc);
        }
    }
}