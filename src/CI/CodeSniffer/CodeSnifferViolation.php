<?php declare(strict_types=1);

namespace SymfonyDocsBuilder\CI\CodeSniffer;

class CodeSnifferViolation
{
    private $code;
    private $reasons;

    // information about file / line are missing
    public function __construct(string $code, array $reasons)
    {
        $this->code    = $code;
        $this->reasons = $reasons;
    }

    public function getCode(): string
    {
        return $this->code;
    }

    public function getReasons(): array
    {
        return $this->reasons;
    }
}
