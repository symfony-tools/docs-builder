<?php

/*
 * This file is part of the Docs Builder package.
 * (c) Ryan Weaver <ryan@symfonycasts.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SymfonyDocsBuilder\Tests;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Process\Process;

/**
 * The rest of the test suite drives the command through CommandTester, so it
 * never runs the binary itself. This makes sure the binary at least boots.
 */
class ApplicationTest extends TestCase
{
    public function testTheBinaryRegistersTheBuildDocsCommand()
    {
        $process = new Process([\PHP_BINARY, __DIR__.'/../bin/docs-builder', 'list', '--no-ansi']);
        $process->run();

        $this->assertSame(
            0,
            $process->getExitCode(),
            sprintf("The binary failed to run:\n%s", $process->getErrorOutput())
        );
        $this->assertStringContainsString('build:docs', $process->getOutput());
    }
}
