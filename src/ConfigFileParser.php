<?php

namespace SymfonyDocsBuilder;

/**
 * Parses the docs.json config file
 */
class ConfigFileParser
{
    private $buildConfig;

    public function __construct(BuildConfig $buildConfig)
    {
        $this->buildConfig = $buildConfig;
    }

    public function processConfigFile(string $configPath): void
    {
        if (!file_exists($configPath)) {
            throw new \RuntimeException(sprintf('No config file present at <info>%s</info>', $configPath));

            return;
        }

        $configData = json_decode(file_get_contents($configPath), true);

        $exclude = $configData['exclude'] ?? [];
        $this->buildConfig->setExcludedPaths($exclude);
        unset($configData['exclude']);

        if ($sfVersion = $configData['symfony-version'] ?? false) {
            $this->buildConfig->setSymfonyVersion($sfVersion);
        }
        unset($configData['symfony-version']);

        if (count($configData) > 0) {
            throw new \Exception(sprintf('Unsupported keys in docs.json: %s', implode(', ', array_keys($configData))));
        }
    }
}
