<?php

/*
 * This file is part of the Guides SymfonyExtension package.
 *
 * (c) Wouter de Jong
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SymfonyTools\DocsBuilder\GuidesExtension\Logger;

use Monolog\Formatter\FormatterInterface;
use Monolog\Formatter\LineFormatter;
use Monolog\Handler\AbstractProcessingHandler;
use Monolog\LogRecord;

class TraceHandler extends AbstractProcessingHandler
{
    private array $stack = [];

    #[\Override]
    protected function write(LogRecord $record): void
    {
        $this->stack[] = (string) $record->formatted;
    }

    public function isEmpty(): bool
    {
        return [] === $this->stack;
    }

    public function reset(): void
    {
        $this->stack = [];
    }

    public function toString(): string
    {
        return implode(PHP_EOL, $this->stack);
    }

    protected function getDefaultFormatter(): FormatterInterface
    {
        return new LineFormatter("%level_name%: %message% %context% %extra%\n");
    }
}
