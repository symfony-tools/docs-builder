<?php

namespace SymfonyDocsBuilder\Code;

use Symfony\Component\Console\Style\SymfonyStyle;

trait ConsoleLoggerTrait
{
    /**
     * @var SymfonyStyle|null
     */
    private $consoleLogger;

    public function setConsoleLogger(SymfonyStyle $consoleLogger)
    {
        $this->consoleLogger = $consoleLogger;
    }

    private function logNote(string $message)
    {
        if (null === $this->consoleLogger) {
            return;
        }

        $this->consoleLogger->note($message);
    }

    private function logWarning(string $message)
    {
        if (null === $this->consoleLogger) {
            return;
        }

        $this->consoleLogger->warning($message);
    }

    private function logDebug(string $message)
    {
        if (null === $this->consoleLogger || !$this->consoleLogger->isVerbose()) {
            return;
        }

        $this->consoleLogger->note($message);
    }
}