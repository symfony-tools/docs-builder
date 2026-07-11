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

    public function __construct(?string $symfonyVersion = null)
    {
        $this->application = new BaseApplication();
        $this->buildConfig = new BuildConfig();

        // when no version is given, the default of BuildConfig is kept
        if (null !== $symfonyVersion) {
            $this->buildConfig->setSymfonyVersion($symfonyVersion);
        }
    }

    public function run(InputInterface $input): int
    {
        $inputOption = new InputOption(
            'symfony-version',
            null,
            InputOption::VALUE_REQUIRED,
            'The symfony version of the doc to parse.',
            $this->buildConfig->getSymfonyVersion()
        );
        $this->application->getDefinition()->addOption($inputOption);

        $command = new BuildDocsCommand($this->buildConfig);

        // Application::add() was deprecated in Symfony 7.4 and removed in 8.0,
        // in favor of Application::addCommand(), introduced in 7.4
        if (method_exists($this->application, 'addCommand')) {
            $this->application->addCommand($command);
        } else {
            $this->application->add($command);
        }

        return $this->application->run($input);
    }
}
