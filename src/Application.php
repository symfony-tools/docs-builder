<?php

namespace SymfonyDocsBuilder;

use Symfony\Component\Console\Application as BaseApplication;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use SymfonyDocsBuilder\Command\BuildDocsCommand;
use SymfonyDocsBuilder\Command\CheckUrlsCommand;
use SymfonyDocsBuilder\Command\GithubReleaseCommand;

class Application
{
    private $application;
    private $buildContext;

    public function __construct(string $symfonyVersion)
    {
        $this->application = new BaseApplication();

        $configuration   = [
            'symfony_api_url' => "https://api.symfony.com/%s",
            'php_doc_url' => "https://secure.php.net/manual/en",
            'symfony_doc_url' => "https://symfony.com/doc/%s",
        ];
        $this->buildContext = new BuildContext(
            $symfonyVersion,
            sprintf($configuration['symfony_api_url'], $symfonyVersion),
            $configuration['php_doc_url'],
            sprintf($configuration['symfony_doc_url'], $symfonyVersion)
        );
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
        $this->application->add(new BuildDocsCommand($this->buildContext));
        $this->application->add(new GithubReleaseCommand());

        return $this->application->run($input);
    }
}
