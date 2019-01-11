<?php

namespace SymfonyDocsBuilder\Code\Extractor;

class CodeSource
{
    private $filename;

    private $code;

    public function __construct(string $filename, string $code)
    {
        $this->filename = $filename;
        $this->code = $code;
    }

    public function getFilename(): string
    {
        return $this->filename;
    }

    public function getCode(): string
    {
        return $this->code;
    }
}