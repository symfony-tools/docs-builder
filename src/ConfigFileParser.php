<?php

namespace SymfonyDocsBuilder;

use Symfony\Component\Console\Output\OutputInterface;

/**
 * Parses the docs.json config file
 */
class ConfigFileParser
{
    private $buildConfig;
    private $output;

    public function __construct(BuildConfig $buildConfig, OutputInterface $output)
    {
        $this->buildConfig = $buildConfig;
        $this->output = $output;
    }

    public function processConfigFile(string $sourceDir): void
    {
        $configPath = $sourceDir.'/docs.json';
        if (!file_exists($configPath)) {
            $this->output->writeln(sprintf('No config file present at <info>%s</info>', $configPath));

            return;
        }

        $this->output->writeln(sprintf('Loading config file: <info>%s</info>', $configPath));
        $configData = json_decode(file_get_contents($configPath), true);

        $exclude = $configData['exclude'] ?? [];
        $this->buildConfig->setExcludedPaths($exclude);
        unset($configData['exclude']);

        if (count($configData) > 0) {
            throw new \Exception(sprintf('Unsupported keys in docs.json: %s', implode(', ', array_keys($configData))));
        }
    }
}
