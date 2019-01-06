<?php declare(strict_types=1);

namespace SymfonyDocsBuilder\CI\CodeSniffer;

class CodeSnifferViolationsList implements \Countable
{
    private $violations = [];

    public function add(string $code, array $reasons)
    {
        $this->violations[] = new CodeSnifferViolation($code, $reasons);
    }

    public function count(): int
    {
        return \count($this->violations);
    }

    public function getViolationsAsArray(): array
    {
        return array_map(
            function (CodeSnifferViolation $codeSnifferViolation) {
                return [
                    $codeSnifferViolation->getCode(),
                    implode("\n", $codeSnifferViolation->getReasons())
                ];
            },
            $this->violations
        );
    }
}
