<?php

namespace SymfonyDocs\Builder;

use Doctrine\RST\Nodes\DocumentNode;

class BuildableDocument
{
    private $filename;

    private $currentDirectory;

    private $targetPath;

    private $documentNode;

    public function __construct(string $filename, string $currentDirectory, string $targetPath, DocumentNode $documentNode)
    {
        $this->filename = $filename;
        $this->currentDirectory = $currentDirectory;
        $this->targetPath = $targetPath;
        $this->documentNode = $documentNode;
    }

    public function getFilename(): string
    {
        return $this->filename;
    }

    public function getTargetPath(): string
    {
        return $this->targetPath;
    }

    /**
     * @return \Doctrine\RST\Nodes\Node[]
     */
    public function getNodes(): array
    {
        return $this->documentNode->getNodes();
    }
}