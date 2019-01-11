<?php

namespace SymfonyDocsBuilder\Code;

use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Process\Process;

class GitUtil
{
    use ConsoleLoggerTrait;

    public function clone(string $repoUrl, string $path)
    {
        $process = new Process(['git', 'clone', $repoUrl, $path]);
        $this->mustRun($process);
    }

    public function addGitAuthor(string $path)
    {
        $process = new Process(['git', 'config', 'user.name', 'symfony docs'], $path);
        $this->mustRun($process);

        $process = new Process(['git', 'config', 'user.email', 'build@symfony.com'], $path);
        $this->mustRun($process);
    }

    public function commit(string $path, string $message)
    {
        $process = new Process(['git', 'add', '.'], $path);
        $this->mustRun($process);

        $process = new Process(['git', 'commit', '-a', '-m', $message], $path);
        $this->mustRun($process);
    }

    public function init(string $path)
    {
        $process = new Process(['git', 'init'], $path);
        $this->mustRun($process);
    }

    private function mustRun(Process $process)
    {
        $this->logDebug('Command: '.$process->getCommandLine());
        $process->mustRun();
    }
}
