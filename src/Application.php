<?php

/*
 * This file is part of the Docs Builder package.
 *
 * (c) Ryan Weaver <ryan@symfonycasts.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SymfonyTools\GuidesExtension;

use Symfony\Component\Console\Application as SymfonyApplication;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class Application extends SymfonyApplication
{
    public function __construct(
        private ?OutputInterface $output = null
    ) {
        parent::__construct('Symfony Docs Builder');
    }

    public function run(InputInterface $input = null, OutputInterface $output = null): int
    {
        return parent::run($input, $output ?? $this->output);
    }
}
