<?php

/*
 * This file is part of the Docs Builder package.
 * (c) Ryan Weaver <ryan@symfonycasts.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SymfonyDocsBuilder;

use Symfony\Component\Console\Application as BaseApplication;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use SymfonyDocsBuilder\Command\BuildDocsCommand;

class Application
{
    private $application;
    private $buildConfig;

    public function __construct(string $symfonyVersion)
    {
        $this->application = new BaseApplication();
        $this->buildConfig = new BuildConfig();
    }

    public function run(InputInterface $input): int
    {
        $inputOption = new InputOption(
            'symfony-version',
            null,
            InputOption::VALUE_REQUIRED,
            'The symfony version of the doc to parse.',
            false === getenv('SYMFONY_VERSION') ? 'master' : getenv('SYMFONY_VERSION')
        );
        $this->application->getDefinition()->addOption($inputOption);
        $this->application->add(new BuildDocsCommand($this->buildConfig));

        return $this->application->run($input);
    }
}
